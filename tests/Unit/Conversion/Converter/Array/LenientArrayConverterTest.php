<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\Array;

use ArrayIterator;
use Generator;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\Array\LenientArrayConverter;
use stdClass;
use Traversable;

final class LenientArrayConverterTest extends TestCase
{

	public static bool $alwaysFalse = false;

	private LenientArrayConverter $converter;

	protected function setUp(): void
	{
		$this->converter = new LenientArrayConverter();
	}

	public function testArrayWithEmptyArray(): void
	{
		$this->assertSame([], $this->converter->array([], true));
		$this->assertSame([], $this->converter->array([], false));
	}

	public function testArrayWithIndexedArray(): void
	{
		$array = [1, 2, 3];
		$this->assertSame($array, $this->converter->array($array, true));
		$this->assertSame($array, $this->converter->array($array, false));
	}

	public function testArrayWithAssociativeArray(): void
	{
		$array = ['a' => 1, 'b' => 2, 'c' => 3];
		$this->assertSame($array, $this->converter->array($array, true));
		$this->assertSame($array, $this->converter->array($array, false));
	}

	public function testArrayWithMixedArray(): void
	{
		$array = [1, 'a' => 2, 3, 'b' => 4];
		$this->assertSame($array, $this->converter->array($array, true));
		$this->assertSame($array, $this->converter->array($array, false));
	}

	public function testArrayWithNestedArrays(): void
	{
		$array = [1, [2, 3], ['a' => 4]];
		$this->assertSame($array, $this->converter->array($array, true));
		$this->assertSame($array, $this->converter->array($array, false));
	}

	public function testArrayWithGeneratorPreservingKeys(): void
	{
		$generator = $this->createGenerator();
		$result = $this->converter->array($generator, true);

		$this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $result);
	}

	public function testArrayWithGeneratorNotPreservingKeys(): void
	{
		$generator = $this->createGenerator();
		$result = $this->converter->array($generator, false);

		$this->assertSame([1, 2, 3], $result);
	}

	public function testArrayWithGeneratorNumericKeys(): void
	{
		$generator = $this->createNumericGenerator();
		$result = $this->converter->array($generator, true);

		$this->assertSame([0 => 'zero', 1 => 'one', 2 => 'two'], $result);
	}

	public function testArrayWithEmptyGenerator(): void
	{
		$generator = $this->createEmptyGenerator();
		$result = $this->converter->array($generator, true);

		$this->assertSame([], $result);
	}

	public function testArrayWithArrayIteratorPreservingKeys(): void
	{
		$iterator = new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);
		$result = $this->converter->array($iterator, true);

		$this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $result);
	}

	public function testArrayWithArrayIteratorNotPreservingKeys(): void
	{
		$iterator = new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);
		$result = $this->converter->array($iterator, false);

		$this->assertSame([1, 2, 3], $result);
	}

	public function testArrayWithIteratorAggregatePreservingKeys(): void
	{
		$iteratorAggregate = new class implements IteratorAggregate {
			public function getIterator(): Traversable
			{
				return new ArrayIterator(['x' => 10, 'y' => 20, 'z' => 30]);
			}
		};

		$result = $this->converter->array($iteratorAggregate, true);
		$this->assertSame(['x' => 10, 'y' => 20, 'z' => 30], $result);
	}

	public function testArrayWithIteratorAggregateNotPreservingKeys(): void
	{
		$iteratorAggregate = new class implements IteratorAggregate {
			public function getIterator(): Traversable
			{
				return new ArrayIterator(['x' => 10, 'y' => 20, 'z' => 30]);
			}
		};

		$result = $this->converter->array($iteratorAggregate, false);
		$this->assertSame([10, 20, 30], $result);
	}

	public function testArrayWithStdClassEmptyObject(): void
	{
		$object = new stdClass();
		$result = $this->converter->array($object, true);

		$this->assertSame([], $result);
	}

	public function testArrayWithStdClassWithProperties(): void
	{
		$object = new stdClass();
		$object->a = 1;
		$object->b = 2;
		$object->c = 3;

		$result = $this->converter->array($object, true);
		$this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $result);

		$result = $this->converter->array($object, false);
		$this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $result);
	}

	public function testArrayWithStdClassNestedProperties(): void
	{
		$object = new stdClass();
		$object->a = 1;
		$object->nested = new stdClass();
		$object->nested->b = 2;

		$result = $this->converter->array($object, true);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('a', $result);
		$this->assertArrayHasKey('nested', $result);
		$this->assertSame(1, $result['a']);
		$this->assertInstanceOf(stdClass::class, $result['nested']);
	}

	public function testArrayReturnsNullForNonStdClassObjects(): void
	{
		$object = new class {
			public int $value = 42;
		};

		$this->assertNull($this->converter->array($object, true));
		$this->assertNull($this->converter->array($object, false));
	}

	public function testArrayReturnsNullForStrings(): void
	{
		$this->assertNull($this->converter->array('', true));
		$this->assertNull($this->converter->array('hello', true));
		$this->assertNull($this->converter->array('123', true));
	}

	public function testArrayReturnsNullForIntegers(): void
	{
		$this->assertNull($this->converter->array(0, true));
		$this->assertNull($this->converter->array(1, true));
		$this->assertNull($this->converter->array(42, true));
	}

	public function testArrayReturnsNullForFloats(): void
	{
		$this->assertNull($this->converter->array(0.0, true));
		$this->assertNull($this->converter->array(3.14, true));
	}

	public function testArrayReturnsNullForBooleans(): void
	{
		$this->assertNull($this->converter->array(true, true));
		$this->assertNull($this->converter->array(false, true));
	}

	public function testArrayReturnsNullForNull(): void
	{
		$this->assertNull($this->converter->array(null, true));
	}

	public function testArrayPreserveKeysBehavior(): void
	{
		$iterator = new ArrayIterator([5 => 'a', 10 => 'b', 15 => 'c']);

		$resultPreserved = $this->converter->array($iterator, true);
		$this->assertSame([5 => 'a', 10 => 'b', 15 => 'c'], $resultPreserved);

		$iterator = new ArrayIterator([5 => 'a', 10 => 'b', 15 => 'c']);
		$resultNotPreserved = $this->converter->array($iterator, false);
		$this->assertSame(['a', 'b', 'c'], $resultNotPreserved);
	}

	public function testArrayWithStdClassPreserveKeysParameter(): void
	{
		$object = new stdClass();
		$object->x = 1;
		$object->y = 2;

		$resultPreserved = $this->converter->array($object, true);
		$resultNotPreserved = $this->converter->array($object, false);

		$this->assertSame($resultPreserved, $resultNotPreserved);
		$this->assertSame(['x' => 1, 'y' => 2], $resultPreserved);
	}

	private function createGenerator(): Generator
	{
		yield 'a' => 1;
		yield 'b' => 2;
		yield 'c' => 3;
	}

	private function createNumericGenerator(): Generator
	{
		yield 0 => 'zero';
		yield 1 => 'one';
		yield 2 => 'two';
	}

	private function createEmptyGenerator(): Generator
	{
		if (self::$alwaysFalse) {
			yield 15;
		}
	}

}
