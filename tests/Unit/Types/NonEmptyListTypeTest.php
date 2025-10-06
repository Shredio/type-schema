<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Types\IntType;
use Shredio\TypeSchema\Types\NonEmptyListType;
use Shredio\TypeSchema\Types\StringType;
use Tests\TypeTestCase;

final class NonEmptyListTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new NonEmptyListType(new IntType()), ['array', 'int']);
		yield 'single integer' => [42];
		yield 'multiple integers' => [1, 2, 3, 4, 5];
		yield 'negative integers' => [-1, -2, -3];
		yield 'mixed integers' => [0, -5, 10, -15, 20];
		yield 'large array' => range(1, 100);

		// Iterables that convert to arrays
		yield 'generator' => $this->generator([1, 2, 3]);

		yield $this->typeToTest(new NonEmptyListType(new StringType()), ['array', 'string']);
		yield 'string list single' => ['hello'];
		yield 'string list multiple' => ['hello', 'world', 'test'];
		yield 'string list with empty strings' => ['', 'non-empty', ''];
	}

	protected function getInvalidValues(): iterable
	{
		yield $this->typeToTest(new NonEmptyListType(new IntType()));
		yield 'string value' => 'hello';
		yield 'integer value' => 42;
		yield 'float value' => 3.14;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;

		// Empty array (main difference from ListType)
		yield 'empty array' => [];

		yield 'array iterator' => new \ArrayIterator([1, 2, 3]);

		// Associative arrays (not lists)
		yield 'associative array string keys' => ['key' => 'value'];
		yield 'associative array mixed keys' => [0 => 'first', 'key' => 'second'];
		yield 'sparse array' => [0 => 'first', 2 => 'third'];
		yield 'array starting from 1' => [1 => 'first', 2 => 'second'];

		// Invalid item types for int list
		yield 'list with strings' => ['hello', 'world'];
		yield 'list with mixed types' => [1, 'hello', 3];
		yield 'list with floats' => [1.1, 2.2, 3.3];
		yield 'list with nulls' => [1, null, 3];
		yield 'list with arrays' => [1, [2, 3], 4];

		// Objects and other types
		yield 'stdClass object' => new \stdClass();
		yield 'callable object' => $this->callableObject('hello');
		yield 'closure' => function () {};
		yield 'anonymous class' => new class {};
		yield 'resource' => $this->resource();
	}

}
