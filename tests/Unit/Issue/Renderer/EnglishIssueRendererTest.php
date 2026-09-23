<?php declare(strict_types = 1);

namespace Tests\Unit\Issue\Renderer;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Helper\NumberExclusiveRange;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\RangeExclusiveDecision;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\EmptyValue;
use Shredio\TypeSchema\Issue\ErrorCategory;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\InvalidDate;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\InvalidValue;
use Shredio\TypeSchema\Issue\Issue;
use Shredio\TypeSchema\Issue\ItemCountOutOfRange;
use Shredio\TypeSchema\Issue\MissingKey;
use Shredio\TypeSchema\Issue\NotAllowedValue;
use Shredio\TypeSchema\Issue\NumberOutOfRange;
use Shredio\TypeSchema\Issue\Renderer\EnglishIssueRenderer;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;

#[CoversClass(EnglishIssueRenderer::class)]
final class EnglishIssueRendererTest extends TestCase
{

	/**
	 * @return iterable<string, array{Issue, string}>
	 */
	public static function provideIssues(): iterable
	{
		$string = new TypeDefinition(new IdentifierTypeNode('string'));
		$object = new TypeDefinition(new IdentifierTypeNode('object'));

		yield 'invalid type' => [new InvalidType($string, 42), 'Please provide a valid string.'];
		yield 'invalid type without user safe type' => [new InvalidType($object, 42), 'The provided value is not valid.'];
		yield 'missing key' => [new MissingKey(), 'Please provide a value for this field.'];
		yield 'extra key' => [new ExtraKey(), 'This field is not allowed.'];
		yield 'empty value' => [new EmptyValue(''), 'This value should not be blank.'];
		yield 'not allowed value' => [new NotAllowedValue('x', ['a', 'b']), 'Please choose one of the allowed values.'];
		yield 'invalid date' => [new InvalidDate('not-a-date'), 'Please provide a valid date.'];
		yield 'invalid value' => [new InvalidValue(NAN, 'NaN values are not allowed'), 'The provided value is not valid.'];
		yield 'custom issue' => [new CustomIssue('Name cannot be empty.', 'dev'), 'Name cannot be empty.'];
		yield 'number at least' => [
			new NumberOutOfRange(0, NumberInclusiveRange::fromInts(min: 1), RangeInclusiveDecision::ShouldBeGreaterOrEqual),
			'Must be at least 1.',
		];
		yield 'number at most' => [
			new NumberOutOfRange(11, NumberInclusiveRange::fromInts(max: 10), RangeInclusiveDecision::ShouldBeLessOrEqual),
			'Must be at most 10.',
		];
		yield 'number greater' => [
			new NumberOutOfRange(-1, NumberExclusiveRange::fromInts(0), RangeExclusiveDecision::ShouldBeGreater),
			'Must be greater than 0.',
		];
		yield 'number exact' => [
			new NumberOutOfRange(1, NumberInclusiveRange::exactInt(2), RangeInclusiveDecision::ShouldBeGreaterOrEqual),
			'Must be equal to 2.',
		];
		yield 'item count at least one' => [
			new ItemCountOutOfRange(0, NumberInclusiveRange::fromInts(min: 1), RangeInclusiveDecision::ShouldBeGreaterOrEqual),
			'Must contain at least 1 item.',
		];
		yield 'item count at most' => [
			new ItemCountOutOfRange(5, NumberInclusiveRange::fromInts(max: 3), RangeInclusiveDecision::ShouldBeLessOrEqual),
			'Must contain at most 3 items.',
		];
		yield 'item count exact' => [
			new ItemCountOutOfRange(5, NumberInclusiveRange::exactInt(2), RangeInclusiveDecision::ShouldBeLessOrEqual),
			'Must contain exactly 2 items.',
		];
	}

	#[DataProvider('provideIssues')]
	public function testRender(Issue $issue, string $expectedMessage): void
	{
		$renderer = new EnglishIssueRenderer();

		$this->assertSame($expectedMessage, (string) $renderer->render($issue, new ErrorReportConfig()));
	}

	public function testInvalidTypeHidesExpectedType(): void
	{
		$renderer = new EnglishIssueRenderer();
		$issue = new InvalidType(new TypeDefinition(new IdentifierTypeNode('string')), 42);

		$message = $renderer->render($issue, new ErrorReportConfig(exposeExpectedType: false));

		$this->assertSame('The provided value is not valid.', (string) $message);
	}

	public function testUnknownIssueFallsBackToGenericMessage(): void
	{
		$renderer = new EnglishIssueRenderer();
		$issue = new readonly class extends Issue {
			public function getCategory(): ErrorCategory
			{
				return ErrorCategory::Validation;
			}

			public function getMessageForDeveloper(): string
			{
				return 'dev';
			}
		};

		$this->assertSame('The provided value is not valid.', (string) $renderer->render($issue, new ErrorReportConfig()));
	}

}
