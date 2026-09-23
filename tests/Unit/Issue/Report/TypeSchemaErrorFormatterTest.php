<?php declare(strict_types = 1);

namespace Tests\Unit\Issue\Report;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\IdentifiedPath;
use Shredio\TypeSchema\Issue\IssueCollection;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Issue\Path;
use Shredio\TypeSchema\Issue\Report\TypeSchemaErrorFormatter;
use Shredio\TypeSchema\Result\Failure;

#[CoversClass(TypeSchemaErrorFormatter::class)]
final class TypeSchemaErrorFormatterTest extends TestCase
{

	public function testPrettyStringWithSingleError(): void
	{
		$error = new CustomIssue('User msg', 'Developer msg');

		$result = TypeSchemaErrorFormatter::prettyString($error);

		$this->assertSame('✖ Developer msg', $result);
	}

	public function testPrettyStringWithPath(): void
	{
		$error = new IssuePath(
			new CustomIssue('User msg', 'Developer msg'),
			new Path('name'),
		);

		$result = TypeSchemaErrorFormatter::prettyString($error);

		$this->assertStringContainsString('Developer msg', $result);
		$this->assertStringContainsString('at name', $result);
	}

	public function testPrettyStringWithIdentifiedPath(): void
	{
		$error = new IssuePath(
			new CustomIssue('User msg', 'Developer msg'),
			new Path('users', new IdentifiedPath(42)),
		);

		$result = TypeSchemaErrorFormatter::prettyString($error);

		$this->assertStringContainsString('Developer msg', $result);
		$this->assertStringContainsString('at users', $result);
		$this->assertStringContainsString('for value 42', $result);
	}

	public function testPrettyStringWithMultipleErrors(): void
	{
		$error = new IssueCollection([
			new IssuePath(new CustomIssue('msg1', 'dev1'), new Path('field1')),
			new IssuePath(new CustomIssue('msg2', 'dev2'), new Path('field2')),
		]);

		$result = TypeSchemaErrorFormatter::prettyString($error);

		$this->assertStringContainsString('dev1', $result);
		$this->assertStringContainsString('dev2', $result);
		$this->assertStringContainsString('field1', $result);
		$this->assertStringContainsString('field2', $result);
	}

	public function testPrettyStringWithCustomStyles(): void
	{
		$error = new IssuePath(
			new CustomIssue('User msg', 'Developer msg'),
			new Path('name'),
		);

		$result = TypeSchemaErrorFormatter::prettyString($error, '- ', '> ');

		$this->assertStringStartsWith('- Developer msg', $result);
		$this->assertStringContainsString('> at name', $result);
	}

	public function testPrettyStringWithoutPath(): void
	{
		$error = new CustomIssue('User msg', 'Developer msg');

		$result = TypeSchemaErrorFormatter::prettyString($error, '', '');

		$this->assertSame('Developer msg', $result);
	}

	public function testPrettyStringOfFailureFormatsOnlyErrors(): void
	{
		$failure = new Failure(
			new IssuePath(new CustomIssue('User msg', 'Developer msg'), new Path('name')),
			new IssuePath(new ExtraKey(), new Path('extra')),
		);

		$result = TypeSchemaErrorFormatter::prettyString($failure, '');

		$this->assertSame("Developer msg\n  → at name", $result);
	}

}
