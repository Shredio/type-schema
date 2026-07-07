<?php declare(strict_types = 1);

namespace Tests\Unit\Validation;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Error\ErrorCategory;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Validation\EnglishErrorElementFactory;

#[CoversClass(EnglishErrorElementFactory::class)]
final class EnglishErrorElementFactoryTest extends TestCase
{

	public function testInvalidTypeIsStructural(): void
	{
		$factory = new EnglishErrorElementFactory();
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = $factory->invalidType($definition, 42);

		$this->assertSame(ErrorCategory::Structural, $error->getReports()[0]->category);
	}

	public function testMissingFieldIsStructural(): void
	{
		$factory = new EnglishErrorElementFactory();
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = $factory->missingField($definition);

		$this->assertSame(ErrorCategory::Structural, $error->getReports()[0]->category);
	}

	public function testExtraFieldIsStructural(): void
	{
		$factory = new EnglishErrorElementFactory();
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = $factory->extraField($definition);

		$this->assertSame(ErrorCategory::Structural, $error->getReports()[0]->category);
	}

	public function testNotEmptyIsValidation(): void
	{
		$factory = new EnglishErrorElementFactory();
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = $factory->notEmpty($definition, '');

		$this->assertSame(ErrorCategory::Validation, $error->getReports()[0]->category);
		$this->assertSame('This value should not be blank.', (string) $error->getReports()[0]->message);
	}

	public function testNumberRangeIsValidation(): void
	{
		$factory = new EnglishErrorElementFactory();
		$definition = new TypeDefinition(new IdentifierTypeNode('int'));
		$error = $factory->numberRange(
			$definition,
			0,
			NumberInclusiveRange::fromInts(min: 1),
			RangeInclusiveDecision::ShouldBeGreaterOrEqual,
		);

		$this->assertSame(ErrorCategory::Validation, $error->getReports()[0]->category);
	}

	public function testValueNotInAllowedValuesIsValidation(): void
	{
		$factory = new EnglishErrorElementFactory();
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = $factory->valueNotInAllowedValues($definition, 'x', ['a', 'b']);

		$this->assertSame(ErrorCategory::Validation, $error->getReports()[0]->category);
	}

	public function testEqualToIsValidation(): void
	{
		$factory = new EnglishErrorElementFactory();
		$definition = new TypeDefinition(new IdentifierTypeNode('int'));
		$error = $factory->equalTo($definition, 1, '2');

		$this->assertSame(ErrorCategory::Validation, $error->getReports()[0]->category);
	}

	public function testInvalidDateIsValidation(): void
	{
		$factory = new EnglishErrorElementFactory();
		$error = $factory->invalidDate('not-a-date');

		$this->assertSame(ErrorCategory::Validation, $error->getReports()[0]->category);
	}

	public function testItemCountRangeIsValidation(): void
	{
		$factory = new EnglishErrorElementFactory();
		$definition = new TypeDefinition(new IdentifierTypeNode('list'));
		$error = $factory->itemCountRange(
			$definition,
			0,
			NumberInclusiveRange::fromInts(min: 1),
			RangeInclusiveDecision::ShouldBeGreaterOrEqual,
		);

		$this->assertSame(ErrorCategory::Validation, $error->getReports()[0]->category);
	}

	public function testInvalidValueIsValidation(): void
	{
		$factory = new EnglishErrorElementFactory();
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = $factory->invalidValue($definition, 'x', 'developer message');

		$this->assertSame(ErrorCategory::Validation, $error->getReports()[0]->category);
	}

	public function testCreateErrorDefaultsToValidation(): void
	{
		$factory = new EnglishErrorElementFactory();
		$error = $factory->createError('user', 'developer');

		$this->assertSame(ErrorCategory::Validation, $error->getReports()[0]->category);
	}

	public function testCreateErrorRespectsExplicitCategory(): void
	{
		$factory = new EnglishErrorElementFactory();
		$error = $factory->createError('user', 'developer', ErrorCategory::Structural);

		$this->assertSame(ErrorCategory::Structural, $error->getReports()[0]->category);
	}

}
