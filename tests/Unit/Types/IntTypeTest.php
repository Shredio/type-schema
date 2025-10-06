<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Types\IntType;
use Tests\TypeTestCase;

final class IntTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new IntType(), ['int']);
		yield 'zero' => 0;
		yield 'positive one' => 1;
		yield 'negative one' => -1;
		yield 'positive integer' => 123;
		yield 'negative integer' => -456;
		yield 'maximum integer' => PHP_INT_MAX;
		yield 'minimum integer' => PHP_INT_MIN;
	}

	protected function getInvalidValues(): iterable
	{
		yield $this->typeToTest(new IntType());
		yield 'empty string' => '';
		yield 'text string' => 'hello';
		yield 'numeric string' => '123';
		yield 'positive float' => 12.34;
		yield 'negative float' => -45.67;
		yield 'NAN value' => NAN;
		yield 'INF value' => INF;
		yield 'negative INF value' => -INF;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;
		yield 'empty array' => [];
		yield 'stdClass object' => new \stdClass();
		yield 'stringable object' => $this->stringable('123');
		yield 'callable object' => $this->callableObject('hello');
		yield 'closure' => function () {};
		yield 'anonymous class' => new class {};
		yield 'resource' => $this->resource();
		yield 'generator' => $this->generator([123]);
		yield 'array iterator' => new \ArrayIterator(['hello']);
	}

}
