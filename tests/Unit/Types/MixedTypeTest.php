<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Types\MixedType;
use Tests\TypeTestCase;

final class MixedTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new MixedType(), []);
		yield 'string' => 'hello';
		yield 'empty string' => '';
		yield 'integer' => 123;
		yield 'zero' => 0;
		yield 'negative integer' => -456;
		yield 'float' => 45.67;
		yield 'negative float' => -12.34;
		yield 'zero float' => 0.0;
		yield 'NAN value' => NAN;
		yield 'INF value' => INF;
		yield 'negative INF value' => -INF;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;
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
		yield 'unicode emoji' => '🚀';
		yield 'multi-byte string' => $this->multiByteString();
	}

	protected function getInvalidValues(): iterable
	{
		// MixedType accepts everything, so no invalid values
		return [];
	}

	protected function hasInvalidTypes(): bool
	{
		return false;
	}

}
