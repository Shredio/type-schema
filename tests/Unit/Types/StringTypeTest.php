<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Types\StringType;
use Tests\TypeTestCase;

final class StringTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new StringType(), ['string']);
		yield 'empty string' => '';
		yield 'regular string' => 'hello';
		yield 'numeric string' => '123';
		yield 'boolean string true' => 'true';
		yield 'boolean string false' => 'false';
		yield 'null string' => 'null';
		yield 'single space' => ' ';
		yield 'newline character' => "\n";
		yield 'tab character' => "\t";
		yield 'unicode emoji' => '🚀';
		yield 'multi-byte string' => $this->multiByteString();
	}

	protected function getInvalidValues(): iterable
	{
		yield $this->typeToTest(new StringType());
		yield 'integer' => 123;
		yield 'float' => 45.67;
		yield 'NAN value' => NAN;
		yield 'INF value' => INF;
		yield 'negative INF value' => -INF;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;
		yield 'empty array' => [];
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
