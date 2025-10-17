<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Types\ArrayType;
use Shredio\TypeSchema\Types\IntType;
use Shredio\TypeSchema\Types\StringType;
use Tests\TypeTestCase;

final class ArrayTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new ArrayType(new StringType(), new IntType()), ['array', 'string', 'int']);
		yield 'associative array string keys' => ['first' => 1, 'second' => 2, 'third' => 3];
		yield 'single element' => ['key' => 42];

		yield $this->typeToTest(new ArrayType(new StringType(), new IntType()), ['array']);
		yield 'empty array' => [];

		yield $this->typeToTest(new ArrayType(new IntType(), new StringType()), ['array', 'int', 'string']);
		yield 'integer keys with strings' => [0 => 'hello', 1 => 'world', 2 => 'test'];
		yield 'sparse array' => [0 => 'first', 5 => 'second', 10 => 'third'];
		yield 'negative keys' => [-1 => 'negative', 0 => 'zero', 1 => 'positive'];

		yield $this->typeToTest(new ArrayType(new StringType(), new StringType()), ['array', 'string']);
		yield 'string to string' => ['name' => 'John', 'city' => 'Prague'];
		yield 'mixed key format' => ['key-1' => 'value1', 'key_2' => 'value2', 'key3' => 'value3'];

		yield $this->typeToTest(new ArrayType(new IntType(), new IntType()), ['array', 'int']);
		yield 'int to int' => [1 => 100, 2 => 200, 3 => 300];
		yield 'list-like array' => [0 => 10, 1 => 20, 2 => 30];
		yield 'generator with int keys' => $this->generator([10, 20, 30]);
	}

	protected function getInvalidValues(): iterable
	{
		yield $this->typeToTest(new ArrayType(new StringType(), new IntType()));
		yield 'string value' => 'hello';
		yield 'integer value' => 42;
		yield 'float value' => 3.14;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;

		// Invalid value types
		yield 'array with string values instead of int' => ['key' => 'not-an-int'];
		yield 'array with mixed values' => ['key1' => 1, 'key2' => 'string'];

		yield $this->typeToTest(new ArrayType(new IntType(), new StringType()));
		// Invalid key types
		yield 'array with string keys instead of int' => ['key' => 'value'];
		yield 'array with mixed key types' => [0 => 'first', 'key' => 'second'];

		yield $this->typeToTest(new ArrayType(new StringType(), new IntType()));
		yield 'array iterator' => new \ArrayIterator(['key' => 1]);

		// Objects and other types
		yield 'stdClass object' => new \stdClass();
		yield 'callable object' => $this->callableObject('hello');
		yield 'closure' => function () {};
		yield 'anonymous class' => new class {};
		yield 'resource' => $this->resource();
	}

}
