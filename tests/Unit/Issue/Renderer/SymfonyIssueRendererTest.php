<?php declare(strict_types = 1);

namespace Tests\Unit\Issue\Renderer;

use LogicException;
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
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\InvalidDate;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\InvalidValue;
use Shredio\TypeSchema\Issue\Issue;
use Shredio\TypeSchema\Issue\ItemCountOutOfRange;
use Shredio\TypeSchema\Issue\MissingKey;
use Shredio\TypeSchema\Issue\NotAllowedValue;
use Shredio\TypeSchema\Issue\NumberOutOfRange;
use Shredio\TypeSchema\Issue\Renderer\SymfonyIssueRenderer;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Translation\Translator;

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
		yield 'number greater' => [
			new NumberOutOfRange(0, NumberExclusiveRange::fromInts(min: 0), RangeExclusiveDecision::ShouldBeGreater),
			'This value should be greater than 0.',
		];
		yield 'number less' => [
			new NumberOutOfRange(10, NumberExclusiveRange::fromInts(max: 10), RangeExclusiveDecision::ShouldBeLess),
			'This value should be less than 10.',
		];
		yield 'number at most' => [
			new NumberOutOfRange(11, NumberInclusiveRange::fromInts(max: 10), RangeInclusiveDecision::ShouldBeLessOrEqual),
			'This value should be less than or equal to 10.',
		];
		yield 'item count at least one' => [
			new ItemCountOutOfRange(0, NumberInclusiveRange::fromInts(min: 1), RangeInclusiveDecision::ShouldBeGreaterOrEqual),
			'This collection should contain 1 element or more.',
		];
		yield 'item count at least many' => [
			new ItemCountOutOfRange(0, NumberInclusiveRange::fromInts(min: 3), RangeInclusiveDecision::ShouldBeGreaterOrEqual),
			'This collection should contain 3 elements or more.',
		];
		yield 'item count at most one' => [
			new ItemCountOutOfRange(2, NumberInclusiveRange::fromInts(max: 1), RangeInclusiveDecision::ShouldBeLessOrEqual),
			'This collection should contain 1 element or less.',
		];
		yield 'item count at most many' => [
			new ItemCountOutOfRange(9, NumberInclusiveRange::fromInts(max: 5), RangeInclusiveDecision::ShouldBeLessOrEqual),
			'This collection should contain 5 elements or less.',
		];
		yield 'item count exact one' => [
			new ItemCountOutOfRange(2, NumberInclusiveRange::exactInt(1), RangeInclusiveDecision::ShouldBeLessOrEqual),
			'This collection should contain exactly 1 element.',
		];
		yield 'item count exact many' => [
			new ItemCountOutOfRange(2, NumberInclusiveRange::exactInt(3), RangeInclusiveDecision::ShouldBeGreaterOrEqual),
			'This collection should contain exactly 3 elements.',
		];
		yield 'invalid type without user safe type' => [
			new InvalidType(new TypeDefinition(new IdentifierTypeNode('object')), 1),
			'This value is not valid.',
		];
		yield 'invalid value' => [new InvalidValue(NAN, 'NaN values are not allowed'), 'This value is not valid.'];
	}

	/**
	 * @return iterable<string, array{Issue}>
	 */
	public static function provideIssuesWithOkDecision(): iterable
	{
		yield 'number' => [new NumberOutOfRange(1, NumberInclusiveRange::fromInts(min: 0), RangeInclusiveDecision::Ok)];
		yield 'item count' => [new ItemCountOutOfRange(1, NumberInclusiveRange::fromInts(min: 0), RangeInclusiveDecision::Ok)];
	}

	#[DataProvider('provideIssuesWithOkDecision')]
	public function testOkDecisionCannotBeRendered(Issue $issue): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Unknown range decision Ok.');

		(new SymfonyIssueRenderer(new IdentityTranslator()))->render($issue, new ErrorReportConfig());
	}

	public function testInvalidTypeHidesExpectedType(): void
	{
		$renderer = new SymfonyIssueRenderer(new IdentityTranslator());
		$issue = new InvalidType(new TypeDefinition(new IdentifierTypeNode('int')), 'abc');

		$this->assertSame('This value is not valid.', (string) $renderer->render($issue, new ErrorReportConfig(exposeExpectedType: false)));
	}

	public function testMessagesAreTranslatedInConfiguredDomain(): void
	{
		$translator = new Translator('cs');
		$translator->addLoader('array', new ArrayLoader());
		$translator->addResource('array', [
			'This field is missing.' => 'Toto pole chybí.',
			'This collection should contain {{ limit }} element or more.|This collection should contain {{ limit }} elements or more.'
				=> 'Kolekce musí obsahovat alespoň {{ limit }} prvek.|Kolekce musí obsahovat alespoň {{ limit }} prvky.|Kolekce musí obsahovat alespoň {{ limit }} prvků.',
		], 'cs', 'forms');
		$renderer = new SymfonyIssueRenderer($translator, 'forms');
		$config = new ErrorReportConfig();

		$this->assertSame('Toto pole chybí.', (string) $renderer->render(new MissingKey(), $config));
		$this->assertSame(
			'Kolekce musí obsahovat alespoň 3 prvky.',
			(string) $renderer->render(new ItemCountOutOfRange(0, NumberInclusiveRange::fromInts(min: 3), RangeInclusiveDecision::ShouldBeGreaterOrEqual), $config),
		);
		$this->assertSame('This field was not expected.', (string) $renderer->render(new ExtraKey(), $config));
	}

	#[DataProvider('provideIssues')]
	public function testRender(Issue $issue, string $expectedMessage): void
	{
		$renderer = new SymfonyIssueRenderer(new IdentityTranslator());

		$this->assertSame($expectedMessage, (string) $renderer->render($issue, new ErrorReportConfig()));
	}

	public function testRenderTranslatesTranslatableCustomMessageInItsOwnDomain(): void
	{
		$translator = new Translator('en');
		$translator->addLoader('array', new ArrayLoader());
		$translator->addResource('array', ['coupon.invalid' => 'The coupon {code} is not valid.'], 'en', 'account');
		$renderer = new SymfonyIssueRenderer($translator);
		$issue = new CustomIssue(new TranslatableMessage('coupon.invalid', ['{code}' => 'ABC'], 'account'));

		$this->assertSame('The coupon ABC is not valid.', $renderer->render($issue, new ErrorReportConfig()));
	}

}
