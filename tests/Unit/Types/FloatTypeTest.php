<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Types\FloatType;
use Tests\TypeTestCase;

final class FloatTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new FloatType(), ['float']);
		yield 'zero float' => 0.0;
		yield 'positive float' => 12.34;
		yield 'negative float' => -45.67;
		yield 'small float' => 0.0001;
		yield 'large float' => 999999.999999;
		yield 'scientific notation' => 1.23e-10;
		yield 'PI constant' => M_PI;
		yield 'E constant' => M_E;
		yield 'INF value' => INF;
		yield 'negative INF value' => -INF;

		// Test allowNan=true
		yield $this->typeToTest(new FloatType(allowNan: true), ['float']);
		yield 'NAN value with allowNan=true' => NAN;

		// Test allowInf=false (but normal floats still valid)
		yield $this->typeToTest(new FloatType(allowInf: false), ['float']);
		yield 'normal float with allowInf=false' => 42.5;
		yield 'zero with allowInf=false' => 0.0;
		yield 'negative with allowInf=false' => -123.45;
	}

	protected function getInvalidValues(): iterable
	{
		yield $this->typeToTest(new FloatType());
		yield 'integer zero' => 0;
		yield 'positive integer' => 123;
		yield 'negative integer' => -456;
		yield 'empty string' => '';
		yield 'text string' => 'hello';
		yield 'numeric string' => '12.34';
		yield 'float string' => '45.67';
		yield 'NAN value' => NAN;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;
		yield 'empty array' => [];
		yield 'array with values' => [1.23, 4.56];
		yield 'stdClass object' => new \stdClass();
		yield 'stringable object' => $this->stringable('12.34');
		yield 'callable object' => $this->callableObject('hello');
		yield 'closure' => function () {};
		yield 'anonymous class' => new class {};
		yield 'resource' => $this->resource();
		yield 'generator' => $this->generator([1.23]);
		yield 'array iterator' => new \ArrayIterator([1.23]);

		// Test allowInf=false - INF values should be invalid
		yield $this->typeToTest(new FloatType(allowInf: false));
		yield 'INF with allowInf=false' => INF;
		yield 'negative INF with allowInf=false' => -INF;

		// Test allowNan=false (default) - NAN values should be invalid
		yield $this->typeToTest(new FloatType(allowNan: false));
		yield 'NAN with allowNan=false' => NAN;
	}

}
