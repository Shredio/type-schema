<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\Array;

use Generator;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\Array\StrictArrayConverter;

final class StrictArrayConverterTest extends TestCase
{

	public static bool $alwaysFalse = false;

	private StrictArrayConverter $converter;

	protected function setUp(): void
	{
		$this->converter = new StrictArrayConverter();
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

	public function testArrayReturnsNullForIteratorAggregate(): void
	{
		$iterator = new \ArrayIterator([1, 2, 3]);
		$this->assertNull($this->converter->array($iterator, true));
		$this->assertNull($this->converter->array($iterator, false));
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

	public function testArrayReturnsNullForObjects(): void
	{
		$this->assertNull($this->converter->array(new \stdClass(), true));
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