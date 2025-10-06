<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Types\ObjectType;
use Tests\TypeTestCase;

final class ObjectTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new ObjectType());
		yield 'stdClass object' => new \stdClass();
		yield 'anonymous class' => new class {};
		yield 'DateTime object' => new \DateTime();
		yield 'ArrayIterator object' => new \ArrayIterator([]);
		yield 'Exception object' => new \Exception();
		yield 'callable object' => $this->callableObject('test');
		yield 'stringable object' => $this->stringable('test');
		yield 'closure' => function () {};
		yield 'generator' => $this->generator(['test']);

		yield $this->typeToTest(new ObjectType(\DateTime::class));
		yield 'DateTime object' => new \DateTime();

		yield $this->typeToTest(new ObjectType(\Iterator::class));
		yield 'ArrayIterator object' => new \ArrayIterator([]);
		yield 'generator' => $this->generator(['test']);
	}

	protected function getInvalidValues(): iterable
	{
		yield $this->typeToTest(new ObjectType());
		yield 'integer' => 123;
		yield 'float' => 45.67;
		yield 'NAN value' => NAN;
		yield 'INF value' => INF;
		yield 'negative INF value' => -INF;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;
		yield 'string' => 'hello';
		yield 'empty string' => '';
		yield 'numeric string' => '123';
		yield 'empty array' => [];
		yield 'resource' => $this->resource();

		// specific class
		yield $this->typeToTest(new ObjectType(\DateTime::class));
		yield 'stdClass object' => new \stdClass();
		yield 'DateTimeImmutable object' => new \DateTimeImmutable();
		yield 'not an object' => 'not an object';
		yield 'integer' => 123;

		// interface
		yield $this->typeToTest(new ObjectType(\Iterator::class));
		yield 'stdClass object' => new \stdClass();
		yield 'DateTime object' => new \DateTime();
		yield 'not an object' => 'not an object';
		yield 'integer' => 123;
	}

}
