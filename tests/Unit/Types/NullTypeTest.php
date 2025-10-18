<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Types\NullType;
use Tests\TypeTestCase;

final class NullTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new NullType(), ['null']);
		yield 'null value' => null;
	}

	protected function getInvalidValues(): iterable
	{
		yield $this->typeToTest(new NullType());
		yield 'empty string' => '';
		yield 'text string' => 'hello';
		yield 'numeric string' => '123';
		yield 'zero integer' => 0;
		yield 'positive integer' => 123;
		yield 'negative integer' => -456;
		yield 'positive float' => 12.34;
		yield 'negative float' => -45.67;
		yield 'zero float' => 0.0;
		yield 'NAN value' => NAN;
		yield 'INF value' => INF;
		yield 'negative INF value' => -INF;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'empty array' => [];
		yield 'indexed array' => [1, 2, 3];
		yield 'associative array' => ['a' => 1, 'b' => 2];
		yield 'stdClass object' => new \stdClass();
		yield 'stringable object' => $this->stringable('hello');
		yield 'callable object' => $this->callableObject('hello');
		yield 'closure' => function () {};
		yield 'anonymous class' => new class {};
		yield 'resource' => $this->resource();
		yield 'generator' => $this->generator(['hello']);
		yield 'array iterator' => new \ArrayIterator(['hello']);
	}

}
