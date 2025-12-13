<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Enum\ExtraKeysBehavior;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Types\ArrayShapeType;
use Shredio\TypeSchema\Types\IntType;
use Shredio\TypeSchema\Types\OptionalType;
use Shredio\TypeSchema\Types\StringType;
use stdClass;
use Tests\TypeTestCase;

final class ArrayShapeTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		// Simple shape with required string and int
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
			'age' => new IntType(),
		]), ['array', 'string', 'int']);
		yield 'basic shape' => ['name' => 'John', 'age' => 30];
		yield 'shape with different values' => ['name' => 'Jane', 'age' => 25];

		// Shape with optional field
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
			'nickname' => new OptionalType(new StringType()),
		]), ['array', 'string']);
		yield 'shape without optional field' => ['name' => 'John'];
		yield 'shape with optional field present' => ['name' => 'John', 'nickname' => 'Johnny'];

		// Shape with all optional fields - empty
		yield $this->typeToTest(new ArrayShapeType([
			'first' => new OptionalType(new StringType()),
			'second' => new OptionalType(new IntType()),
		]), ['array']);
		yield 'empty shape when all optional' => [];

		// Shape with all optional fields - partial
		yield $this->typeToTest(new ArrayShapeType([
			'first' => new OptionalType(new StringType()),
			'second' => new OptionalType(new IntType()),
		]), ['array', 'string']);
		yield 'partial optional shape' => ['first' => 'hello'];

		// Shape with all optional fields - full
		yield $this->typeToTest(new ArrayShapeType([
			'first' => new OptionalType(new StringType()),
			'second' => new OptionalType(new IntType()),
		]), ['array', 'string', 'int']);
		yield 'full optional shape' => ['first' => 'hello', 'second' => 42];

		// Shape with integer keys
		yield $this->typeToTest(new ArrayShapeType([
			0 => new StringType(),
			1 => new IntType(),
		]), ['array', 'string', 'int']);
		yield 'integer keyed shape' => [0 => 'value', 1 => 100];

		// Shape with ExtraKeysBehavior::Accept
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
		], ExtraKeysBehavior::Accept), ['array', 'string']);
		yield 'shape accepting extra keys' => ['name' => 'John', 'extra' => 'allowed'];
		yield 'shape accepting multiple extra keys' => ['name' => 'John', 'extra1' => 'a', 'extra2' => 'b'];

		// Shape with ExtraKeysBehavior::Ignore
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
		], ExtraKeysBehavior::Ignore), ['array', 'string']);
		yield 'shape ignoring extra keys' => ['name' => 'John', 'ignored' => 'value'];

		// Complex mixed shape without optional metadata
		yield $this->typeToTest(new ArrayShapeType([
			'id' => new IntType(),
			'name' => new StringType(),
			'metadata' => new OptionalType(new ArrayShapeType([
				'created' => new StringType(),
			])),
		]), ['array', 'int', 'string']);
		yield 'complex shape without optional' => ['id' => 1, 'name' => 'Test'];
	}

	protected function getInvalidValues(): iterable
	{
		// Required fields missing
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
			'age' => new IntType(),
		]));
		yield 'missing required field name' => ['age' => 30];
		yield 'missing required field age' => ['name' => 'John'];
		yield 'empty array when fields required' => [];

		// Wrong types for fields
		yield 'wrong type for name' => ['name' => 123, 'age' => 30];
		yield 'wrong type for age' => ['name' => 'John', 'age' => 'thirty'];
		yield 'null for required field' => ['name' => null, 'age' => 30];

		// Extra keys with Reject behavior (default)
		yield 'extra key with reject behavior' => ['name' => 'John', 'age' => 30, 'extra' => 'rejected'];
		yield 'multiple extra keys' => ['name' => 'John', 'age' => 30, 'a' => 1, 'b' => 2];

		// Non-array values
		yield 'string value' => 'hello';
		yield 'integer value' => 42;
		yield 'float value' => 3.14;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;
		yield 'stdClass object' => new stdClass();
		yield 'callable object' => $this->callableObject('hello');
		yield 'closure' => function () {};
		yield 'anonymous class' => new class {};
		yield 'resource' => $this->resource();

		// Optional field with wrong type
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
			'nickname' => new OptionalType(new StringType()),
		]));
		yield 'optional field with wrong type' => ['name' => 'John', 'nickname' => 123];

		// Nested shape validation
		yield $this->typeToTest(new ArrayShapeType([
			'user' => new ArrayShapeType([
				'name' => new StringType(),
			]),
		]));
		yield 'nested shape with wrong type' => ['user' => 'not-an-array'];
		yield 'nested shape with missing field' => ['user' => []];
		yield 'nested shape with extra field rejected' => ['user' => ['name' => 'John', 'extra' => 'value']];
	}

	public function testIgnoringExtraKeys(): void
	{
		$type = new ArrayShapeType([
			'name' => new StringType(),
		], ExtraKeysBehavior::Ignore);


		$processor = $this->getProcessor();

		$value = ['name' => 'John', 'extra' => 'should be ignored'];
		$ret = $processor->parse($value, $type);

		$this->assertIsArray($ret);
		$this->assertArrayHasKey('name', $ret);
		$this->assertArrayNotHasKey('extra', $ret);
	}

	public function testAllowingExtraKeys(): void
	{
		$type = new ArrayShapeType([
			'name' => new StringType(),
		], ExtraKeysBehavior::Accept);

		$processor = $this->getProcessor();

		$value = ['name' => 'John', 'extra' => 'should be accepted'];
		$ret = $processor->parse($value, $type);

		$this->assertIsArray($ret);
		$this->assertArrayHasKey('name', $ret);
		$this->assertArrayHasKey('extra', $ret);
	}

}
