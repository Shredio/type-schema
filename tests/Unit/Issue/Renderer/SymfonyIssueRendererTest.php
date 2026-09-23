<?php declare(strict_types = 1);

namespace Tests\Unit\Issue\Renderer;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\EmptyValue;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\InvalidDate;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\Issue;
use Shredio\TypeSchema\Issue\MissingKey;
use Shredio\TypeSchema\Issue\NotAllowedValue;
use Shredio\TypeSchema\Issue\NumberOutOfRange;
use Shredio\TypeSchema\Issue\Renderer\SymfonyIssueRenderer;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;
use Symfony\Component\Translation\IdentityTranslator;

#[CoversClass(SymfonyIssueRenderer::class)]
final class SymfonyIssueRendererTest extends TestCase
{

	/**
	 * @return iterable<string, array{Issue, string}>
	 */
	public static function provideIssues(): iterable
	{
		yield 'invalid type' => [
			new InvalidType(new TypeDefinition(new IdentifierTypeNode('int')), 'abc'),
			'This value should be of type int.',
		];
		yield 'missing key' => [new MissingKey(), 'This field is missing.'];
		yield 'extra key' => [new ExtraKey(), 'This field was not expected.'];
		yield 'empty value' => [new EmptyValue(''), 'This value should not be blank.'];
		yield 'not allowed value' => [new NotAllowedValue('x', ['a']), 'The value you selected is not a valid choice.'];
		yield 'invalid date' => [new InvalidDate('x'), 'This value is not a valid date.'];
		yield 'custom issue' => [new CustomIssue('Translated elsewhere.'), 'Translated elsewhere.'];
		yield 'number at least' => [
			new NumberOutOfRange(0, NumberInclusiveRange::fromInts(min: 1), RangeInclusiveDecision::ShouldBeGreaterOrEqual),
			'This value should be greater than or equal to 1.',
		];
		yield 'number exact' => [
			new NumberOutOfRange(1, NumberInclusiveRange::exactInt(2), RangeInclusiveDecision::ShouldBeGreaterOrEqual),
			'This value should be equal to 2.',
		];
	}

	#[DataProvider('provideIssues')]
	public function testRender(Issue $issue, string $expectedMessage): void
	{
		$renderer = new SymfonyIssueRenderer(new IdentityTranslator());

		$this->assertSame($expectedMessage, (string) $renderer->render($issue, new ErrorReportConfig()));
	}

}
