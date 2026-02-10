<?php declare(strict_types = 1);

namespace Tests\Unit\Symfony;

use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Symfony\SymfonyPropertyConstraints;
use Shredio\TypeSchema\Symfony\SymfonySchemaValidator;
use Shredio\TypeSchema\TypeSchema;
use Symfony\Component\Validator\Validation;
use Tests\TestCase;
use Tests\Unit\Symfony\Fixtures\UserFixture;

final class SymfonyPropertyConstraintsTest extends TestCase
{

	public function testValidValuePassesConstraints(): void
	{
		$type = TypeSchema::get()->string()->validate(new SymfonyPropertyConstraints(UserFixture::class, 'name'));

		$result = $this->parseWithValidator($type, 'hello');

		$this->assertNotInstanceOf(ErrorElement::class, $result);
		$this->assertSame('hello', $result);
	}

	public function testInvalidValueReturnsMultipleErrors(): void
	{
		$type = TypeSchema::get()->string()->validate(new SymfonyPropertyConstraints(UserFixture::class, 'name'));

		$result = $this->parseWithValidator($type, '');

		$this->assertInstanceOf(ErrorElement::class, $result);
		$reports = $result->getReports();
		$this->assertCount(2, $reports);
	}

	public function testInvalidValueReturnsSingleError(): void
	{
		$type = TypeSchema::get()->string()->validate(new SymfonyPropertyConstraints(UserFixture::class, 'name'));

		$result = $this->parseWithValidator($type, 'a');

		$this->assertInstanceOf(ErrorElement::class, $result);
		$reports = $result->getReports();
		$this->assertCount(1, $reports);
		$this->assertSame('This value is too short. It should have 2 characters or more.', (string) $reports[0]->message);
	}

	public function testIntegerConstraint(): void
	{
		$type = TypeSchema::get()->int()->validate(new SymfonyPropertyConstraints(UserFixture::class, 'age'));

		$validResult = $this->parseWithValidator($type, 5);
		$this->assertNotInstanceOf(ErrorElement::class, $validResult);
		$this->assertSame(5, $validResult);

		$invalidResult = $this->parseWithValidator($type, -1);
		$this->assertInstanceOf(ErrorElement::class, $invalidResult);
	}

	public function testThrowsWhenOptionIsMissing(): void
	{
		$type = TypeSchema::get()->string()->validate(new SymfonyPropertyConstraints(UserFixture::class, 'name'));

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage(sprintf('Option "%s" is required to use Symfony constraints.', SymfonySchemaValidator::class));

		$this->getProcessor()->parse('hello', $type);
	}

	public function testWithinArrayShape(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape([
			'name' => $t->string()->validate(new SymfonyPropertyConstraints(UserFixture::class, 'name')),
			'age' => $t->int()->validate(new SymfonyPropertyConstraints(UserFixture::class, 'age')),
		]);

		$validResult = $this->parseWithValidator($type, ['name' => 'John', 'age' => 25]);
		$this->assertNotInstanceOf(ErrorElement::class, $validResult);
		$this->assertSame(['name' => 'John', 'age' => 25], $validResult);

		$invalidResult = $this->parseWithValidator($type, ['name' => '', 'age' => -1]);
		$this->assertInstanceOf(ErrorElement::class, $invalidResult);
	}

	private function parseWithValidator(mixed $type, mixed $value): mixed
	{
		$validator = Validation::createValidatorBuilder()
			->enableAttributeMapping()
			->getValidator();
		$config = new TypeConfig(
			options: TypeConfig::buildOptions([
				new SymfonySchemaValidator($validator),
			]),
		);

		return $this->getProcessor()->parse($value, $type, $config);
	}

}
