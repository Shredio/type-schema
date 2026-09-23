<?php declare(strict_types = 1);

namespace Tests\Unit\Symfony;

use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\Symfony\SymfonyConstraints;
use Shredio\TypeSchema\Symfony\SymfonySchemaValidator;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\Types\Type;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Validation;
use Tests\TestCase;

final class SymfonyConstraintsTest extends TestCase
{

	public function testValidValuePassesConstraints(): void
	{
		$type = TypeSchema::get()->string()->validate(new SymfonyConstraints([
			new NotBlank(),
			new Length(min: 3, max: 50),
		]));

		$result = $this->parseWithValidator($type, 'hello');

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame('hello', $result->value);
	}

	public function testInvalidValueReturnsSingleError(): void
	{
		$type = TypeSchema::get()->string()->validate(new SymfonyConstraints([
			new NotBlank(),
		]));

		$result = $this->parseWithValidator($type, '');

		$this->assertInstanceOf(Failure::class, $result);
		$reports = $result->getReports();
		$this->assertCount(1, $reports);
		$this->assertSame('This value should not be blank.', (string) $reports[0]->message);
	}

	public function testInvalidValueReturnsMultipleErrors(): void
	{
		$type = TypeSchema::get()->string()->validate(new SymfonyConstraints([
			new NotBlank(),
			new Length(min: 5),
		]));

		$result = $this->parseWithValidator($type, '');

		$this->assertInstanceOf(Failure::class, $result);
		$reports = $result->getReports();
		$this->assertCount(2, $reports);
	}

	public function testIntegerConstraint(): void
	{
		$type = TypeSchema::get()->int()->validate(new SymfonyConstraints([
			new Positive(),
		]));

		$validResult = $this->parseWithValidator($type, 5);
		$this->assertInstanceOf(Success::class, $validResult);
		$this->assertSame(5, $validResult->value);

		$invalidResult = $this->parseWithValidator($type, -1);
		$this->assertInstanceOf(Failure::class, $invalidResult);
	}

	public function testThrowsWhenOptionIsMissing(): void
	{
		$type = TypeSchema::get()->string()->validate(new SymfonyConstraints([
			new NotBlank(),
		]));

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage(sprintf('Option "%s" is required to use Symfony constraints.', SymfonySchemaValidator::class));

		$this->getProcessor()->parse('hello', $type);
	}

	public function testWithinArrayShape(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape([
			'name' => $t->string()->validate(new SymfonyConstraints([
				new NotBlank(),
				new Length(min: 2, max: 100),
			])),
			'age' => $t->int()->validate(new SymfonyConstraints([
				new Positive(),
			])),
		]);

		$validResult = $this->parseWithValidator($type, ['name' => 'John', 'age' => 25]);
		$this->assertInstanceOf(Success::class, $validResult);
		$this->assertSame(['name' => 'John', 'age' => 25], $validResult->value);

		$invalidResult = $this->parseWithValidator($type, ['name' => '', 'age' => -1]);
		$this->assertInstanceOf(Failure::class, $invalidResult);
	}

	/**
	 * @param Type<mixed> $type
	 * @return Success<mixed>|Failure
	 */
	private function parseWithValidator(Type $type, mixed $value): Success|Failure
	{
		$validator = Validation::createValidator();
		$config = new TypeConfig(
			options: TypeConfig::buildOptions([
				new SymfonySchemaValidator($validator),
			]),
		);

		return $this->getProcessor()->parse($value, $type, $config);
	}

}