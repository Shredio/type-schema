<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Types\IntRangeType;
use Tests\TypeTestCase;

final class IntRangeTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new IntRangeType(10, 100), ['int']);
		yield 'minimum value' => 10;
		yield 'middle value' => 50;
		yield 'maximum value' => 100;
		yield 'lower quarter value' => 25;
		yield 'upper quarter value' => 75;
	}

	protected function getInvalidValues(): iterable
	{
		yield $this->typeToTest(new IntRangeType(10, 100));
		yield 'below minimum' => 9;
		yield 'above maximum' => 101;
		yield 'negative number' => -1;
		yield 'zero' => 0;
		yield 'large number' => 1000;
		yield 'empty string' => '';
		yield 'text string' => 'hello';
		yield 'numeric string' => '50';
		yield 'float number' => 50.5;
		yield 'NAN value' => NAN;
		yield 'INF value' => INF;
		yield 'negative INF value' => -INF;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;
		yield 'empty array' => [];
		yield 'stdClass object' => new \stdClass();
		yield 'stringable object' => $this->stringable('50');
		yield 'callable object' => $this->callableObject('hello');
		yield 'closure' => function () {};
		yield 'anonymous class' => new class {};
		yield 'resource' => $this->resource();
		yield 'generator' => $this->generator([50]);
		yield 'array iterator' => new \ArrayIterator(['hello']);
	}

}
