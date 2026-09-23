<?php declare(strict_types = 1);

namespace Tests\Unit\Issue\Report;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\ErrorCategory;
use Shredio\TypeSchema\Issue\IdentifiedPath;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\IssueCollection;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Issue\MissingKey;
use Shredio\TypeSchema\Issue\Path;
use Shredio\TypeSchema\Issue\Renderer\SymfonyIssueRenderer;
use Shredio\TypeSchema\Issue\Report\ErrorReport;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;
use Symfony\Component\Translation\IdentityTranslator;

#[CoversClass(ErrorReport::class)]
final class ErrorReportTest extends TestCase
{

	public function testToArrayPathEmpty(): void
	{
		$report = new ErrorReport('msg', 'dev');

		$this->assertSame([], $report->toArrayPath());
	}

	public function testToArrayPathWithStringPaths(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('address'),
			new Path('street'),
		]);

		$this->assertSame(['address', 'street'], $report->toArrayPath());
	}

	public function testToArrayPathWithIntPaths(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('items'),
			new Path(0),
			new Path('name'),
		]);

		$this->assertSame(['items', 0, 'name'], $report->toArrayPath());
	}

	public function testToDebugPathStringEmpty(): void
	{
		$report = new ErrorReport('msg', 'dev');

		$this->assertNull($report->toDebugPathString());
	}

	public function testToDebugPathStringWithSimpleKeys(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('address'),
			new Path('street'),
		]);

		$this->assertSame('address.street', $report->toDebugPathString());
	}

	public function testToDebugPathStringWithIntegerIndex(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('items'),
			new Path(0),
			new Path('name'),
		]);

		$this->assertSame('items.[0].name', $report->toDebugPathString());
	}

	public function testToDebugPathStringEscapesSpecialKeys(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('my-field'),
		]);

		$this->assertSame("'my-field'", $report->toDebugPathString());
	}

	public function testToDebugPathStringEscapesKeyWithSpaces(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('some field'),
		]);

		$this->assertSame("'some field'", $report->toDebugPathString());
	}

	public function testToDebugPathStringDoesNotEscapeSimpleKeys(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('_valid_key'),
			new Path('anotherKey123'),
		]);

		$this->assertSame('_valid_key.anotherKey123', $report->toDebugPathString());
	}

	public function testToDebugPathStringEscapesSingleQuoteInKey(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path("it's"),
		]);

		$this->assertSame("'it\\'s'", $report->toDebugPathString());
	}

	public function testToIdentifiedPathEmpty(): void
	{
		$report = new ErrorReport('msg', 'dev');

		$this->assertNull($report->toIdentifiedPath());
	}

	public function testToIdentifiedPathReturnsNullWhenNoIdentifiers(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('field'),
		]);

		$this->assertNull($report->toIdentifiedPath());
	}

	public function testToIdentifiedPathWithIdentifiers(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('users', new IdentifiedPath(42)),
			new Path('name', new IdentifiedPath('John')),
		]);

		$this->assertSame('42 -> "John"', $report->toIdentifiedPath());
	}

	public function testToIdentifiedPathWithCustomSeparator(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('users', new IdentifiedPath(42)),
			new Path('name', new IdentifiedPath('John')),
		]);

		$this->assertSame('42 / "John"', $report->toIdentifiedPath(' / '));
	}

	public function testToIdentifiedPathReturnsNullIfAnyPathMissesIdentifier(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('users', new IdentifiedPath(42)),
			new Path('name'),
		]);

		$this->assertNull($report->toIdentifiedPath());
	}

	public function testFromIssuesRendersMessagesPathAndCategory(): void
	{
		$missingKey = new MissingKey();
		$node = new IssueCollection([
			new IssuePath($missingKey, new Path('name')),
			new IssuePath(new CustomIssue('Too short.', 'Name is too short.'), new Path('nick')),
		]);

		$reports = ErrorReport::fromIssues($node);

		$this->assertCount(2, $reports);
		$this->assertSame('Please provide a value for this field.', (string) $reports[0]->message);
		$this->assertSame('Key is missing.', (string) $reports[0]->messageForDeveloper);
		$this->assertSame(['name'], $reports[0]->toArrayPath());
		$this->assertSame(ErrorCategory::Structural, $reports[0]->category);
		$this->assertSame($missingKey, $reports[0]->issue);
		$this->assertSame('Too short.', (string) $reports[1]->message);
		$this->assertSame('Name is too short.', (string) $reports[1]->messageForDeveloper);
		$this->assertSame(ErrorCategory::Validation, $reports[1]->category);
	}

	public function testFromIssuesUsesGivenRendererAndConfig(): void
	{
		$issue = new InvalidType(new TypeDefinition(new IdentifierTypeNode('int')), 'abc');
		$renderer = new SymfonyIssueRenderer(new IdentityTranslator());

		$exposed = ErrorReport::fromIssues($issue, $renderer);
		$hidden = ErrorReport::fromIssues($issue, $renderer, new ErrorReportConfig(exposeExpectedType: false));

		$this->assertSame('This value should be of type int.', (string) $exposed[0]->message);
		$this->assertSame('This value is not valid.', (string) $hidden[0]->message);
	}

}
