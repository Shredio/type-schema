<?php declare(strict_types = 1);

namespace Tests\Unit\Issue;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\EmptyValue;
use Shredio\TypeSchema\Issue\ErrorCategory;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\InvalidDate;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\InvalidValue;
use Shredio\TypeSchema\Issue\ItemCountOutOfRange;
use Shredio\TypeSchema\Issue\MissingKey;
use Shredio\TypeSchema\Issue\NotAllowedValue;
use Shredio\TypeSchema\Issue\NumberOutOfRange;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;

final class IssueTest extends TestCase
{

	public function testStructuralIssues(): void
	{
		$this->assertSame(ErrorCategory::Structural, $this->createInvalidType()->getCategory());
		$this->assertSame(ErrorCategory::Structural, (new MissingKey())->getCategory());
		$this->assertSame(ErrorCategory::Structural, (new ExtraKey())->getCategory());
	}

	public function testValidationIssues(): void
	{
		$range = NumberInclusiveRange::fromInts(min: 1);

		$this->assertSame(ErrorCategory::Validation, (new EmptyValue(''))->getCategory());
		$this->assertSame(ErrorCategory::Validation, (new NumberOutOfRange(0, $range, RangeInclusiveDecision::ShouldBeGreaterOrEqual))->getCategory());
		$this->assertSame(ErrorCategory::Validation, (new ItemCountOutOfRange(0, $range, RangeInclusiveDecision::ShouldBeGreaterOrEqual))->getCategory());
		$this->assertSame(ErrorCategory::Validation, (new NotAllowedValue('x', ['a', 'b']))->getCategory());
		$this->assertSame(ErrorCategory::Validation, (new InvalidDate('not-a-date'))->getCategory());
		$this->assertSame(ErrorCategory::Validation, (new InvalidValue(NAN, 'NaN values are not allowed'))->getCategory());
	}

	public function testCustomIssueDefaultsToValidation(): void
	{
		$this->assertSame(ErrorCategory::Validation, (new CustomIssue('user'))->getCategory());
	}

	public function testCustomIssueRespectsExplicitCategory(): void
	{
		$this->assertSame(ErrorCategory::Structural, (new CustomIssue('user', 'developer', ErrorCategory::Structural))->getCategory());
	}

	public function testCustomIssueFallsBackToMessageForDeveloper(): void
	{
		$this->assertSame('user', (new CustomIssue('user'))->getMessageForDeveloper());
		$this->assertSame('developer', (new CustomIssue('user', 'developer'))->getMessageForDeveloper());
	}

	public function testInvalidTypeDeveloperMessageIncludesValueAndExpectedType(): void
	{
		$message = $this->createInvalidType()->getMessageForDeveloper();

		$this->assertSame('Invalid type int with value 42, expected string.', $message);
	}

	public function testInvalidTypeWithDefinitionReturnsNewInstance(): void
	{
		$issue = $this->createInvalidType();
		$changed = $issue->withDefinition(new TypeDefinition(new IdentifierTypeNode('int')));

		$this->assertSame('int', $changed->getUserSafeExpectedType(new ErrorReportConfig()));
		$this->assertSame('string', $issue->getUserSafeExpectedType(new ErrorReportConfig()));
		$this->assertSame(42, $changed->value);
	}

	public function testInvalidTypeHidesExpectedTypeWithConfig(): void
	{
		$config = new ErrorReportConfig(exposeExpectedType: false);

		$this->assertNull($this->createInvalidType()->getUserSafeExpectedType($config));
	}

	public function testInvalidTypeUsesTypeSeparator(): void
	{
		$issue = new InvalidType(new TypeDefinition(new UnionTypeNode([
			new IdentifierTypeNode('string'),
			new IdentifierTypeNode('int'),
		])), null);

		$this->assertSame('string, int', $issue->getUserSafeExpectedType(new ErrorReportConfig(typeSeparator: ', ')));
	}

	public function testNumberOutOfRangeWithExactValueUsesEqualToMessage(): void
	{
		$issue = new NumberOutOfRange(1, NumberInclusiveRange::exactInt(2), RangeInclusiveDecision::ShouldBeGreaterOrEqual);

		$this->assertSame('Value 1 is not equal to 2.', $issue->getMessageForDeveloper());
	}

	public function testGetIssuesReturnsItselfWithPath(): void
	{
		$issue = new MissingKey();

		$issues = $issue->getIssues();

		$this->assertCount(1, $issues);
		$this->assertSame($issue, $issues[0]->issue);
		$this->assertSame([], $issues[0]->path);
	}

	private function createInvalidType(): InvalidType
	{
		return new InvalidType(new TypeDefinition(new IdentifierTypeNode('string')), 42);
	}

}
