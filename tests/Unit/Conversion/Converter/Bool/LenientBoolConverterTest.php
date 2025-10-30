<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\Bool;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\Bool\LenientBoolConverter;

final class LenientBoolConverterTest extends TestCase
{

	private LenientBoolConverter $converter;

	protected function setUp(): void
	{
		$this->converter = new LenientBoolConverter();
	}

	public function testBoolWithBooleanTrue(): void
	{
		$this->assertTrue($this->converter->bool(true));
	}

	public function testBoolWithBooleanFalse(): void
	{
		$this->assertFalse($this->converter->bool(false));
	}

	public function testBoolWithStringTrueValues(): void
	{
		$this->assertTrue($this->converter->bool('true'));
		$this->assertTrue($this->converter->bool('True'));
		$this->assertTrue($this->converter->bool('TRUE'));
		$this->assertTrue($this->converter->bool('TrUe'));
		$this->assertTrue($this->converter->bool('1'));
	}

	public function testBoolWithStringFalseValues(): void
	{
		$this->assertFalse($this->converter->bool('false'));
		$this->assertFalse($this->converter->bool('False'));
		$this->assertFalse($this->converter->bool('FALSE'));
		$this->assertFalse($this->converter->bool('FaLsE'));
		$this->assertFalse($this->converter->bool('0'));
	}

	public function testBoolWithIntegerTrueValue(): void
	{
		$this->assertTrue($this->converter->bool(1));
	}

	public function testBoolWithIntegerFalseValue(): void
	{
		$this->assertFalse($this->converter->bool(0));
	}

	public function testBoolReturnsNullForOtherIntegers(): void
	{
		$this->assertNull($this->converter->bool(-1));
		$this->assertNull($this->converter->bool(2));
		$this->assertNull($this->converter->bool(42));
		$this->assertNull($this->converter->bool(-42));
	}

	public function testBoolReturnsNullForInvalidStrings(): void
	{
		$this->assertNull($this->converter->bool('yes'));
		$this->assertNull($this->converter->bool('no'));
		$this->assertNull($this->converter->bool('on'));
		$this->assertNull($this->converter->bool('off'));
		$this->assertNull($this->converter->bool(''));
		$this->assertNull($this->converter->bool('2'));
		$this->assertNull($this->converter->bool('random'));
	}

	public function testBoolReturnsNullForStringsWithWhitespace(): void
	{
		$this->assertNull($this->converter->bool(' true'));
		$this->assertNull($this->converter->bool('true '));
		$this->assertNull($this->converter->bool(' true '));
		$this->assertNull($this->converter->bool("\ttrue"));
		$this->assertNull($this->converter->bool("true\n"));
		$this->assertNull($this->converter->bool(' 1'));
		$this->assertNull($this->converter->bool('1 '));
	}

	public function testBoolReturnsNullForFloats(): void
	{
		$this->assertNull($this->converter->bool(1.0));
		$this->assertNull($this->converter->bool(0.0));
		$this->assertNull($this->converter->bool(3.14));
	}

	public function testBoolReturnsNullForArrays(): void
	{
		$this->assertNull($this->converter->bool([]));
		$this->assertNull($this->converter->bool([true]));
		$this->assertNull($this->converter->bool([1]));
	}

	public function testBoolReturnsNullForObjects(): void
	{
		$this->assertNull($this->converter->bool(new \stdClass()));
	}

	public function testBoolReturnsNullForNull(): void
	{
		$this->assertNull($this->converter->bool(null));
	}

	public function testBoolWithCustomTrueStringValues(): void
	{
		$converter = new LenientBoolConverter(trueValues: ['yes', 'on', '1']);

		$this->assertTrue($converter->bool('yes'));
		$this->assertTrue($converter->bool('YES'));
		$this->assertTrue($converter->bool('on'));
		$this->assertTrue($converter->bool('ON'));
		$this->assertTrue($converter->bool('1'));

		$this->assertNull($converter->bool('true'));
		$this->assertNull($converter->bool(1));
	}

	public function testBoolWithCustomFalseStringValues(): void
	{
		$converter = new LenientBoolConverter(falseValues: ['no', 'off', '0']);

		$this->assertFalse($converter->bool('no'));
		$this->assertFalse($converter->bool('NO'));
		$this->assertFalse($converter->bool('off'));
		$this->assertFalse($converter->bool('OFF'));
		$this->assertFalse($converter->bool('0'));

		$this->assertNull($converter->bool('false'));
		$this->assertNull($converter->bool(0));
	}

	public function testBoolWithCustomIntegerTrueValues(): void
	{
		$converter = new LenientBoolConverter(trueValues: [1, 2, 100]);

		$this->assertTrue($converter->bool(1));
		$this->assertTrue($converter->bool(2));
		$this->assertTrue($converter->bool(100));

		$this->assertNull($converter->bool('1'));
		$this->assertNull($converter->bool('true'));
		$this->assertFalse($converter->bool(0));
		$this->assertFalse($converter->bool('0'));
		$this->assertFalse($converter->bool('false'));
	}

	public function testBoolWithCustomIntegerFalseValues(): void
	{
		$converter = new LenientBoolConverter(falseValues: [0, -1]);

		$this->assertFalse($converter->bool(0));
		$this->assertFalse($converter->bool(-1));

		$this->assertNull($converter->bool('0'));
		$this->assertNull($converter->bool('false'));
		$this->assertTrue($converter->bool(1));
		$this->assertTrue($converter->bool('1'));
		$this->assertTrue($converter->bool('true'));
	}

	public function testBoolWithMixedCustomValues(): void
	{
		$converter = new LenientBoolConverter(
			trueValues: ['yes', 1, 'y'],
			falseValues: ['no', 0, 'n'],
		);

		$this->assertTrue($converter->bool('yes'));
		$this->assertTrue($converter->bool('Y'));
		$this->assertTrue($converter->bool(1));
		$this->assertFalse($converter->bool('no'));
		$this->assertFalse($converter->bool('N'));
		$this->assertFalse($converter->bool(0));

		$this->assertNull($converter->bool('true'));
		$this->assertNull($converter->bool('false'));
		$this->assertNull($converter->bool('1'));
		$this->assertNull($converter->bool('0'));
		$this->assertNull($converter->bool(2));
	}

	public function testBoolWithEmptyCustomValues(): void
	{
		$converter = new LenientBoolConverter(
			trueValues: [],
			falseValues: [],
		);

		$this->assertNull($converter->bool('true'));
		$this->assertNull($converter->bool('false'));
		$this->assertNull($converter->bool('1'));
		$this->assertNull($converter->bool('0'));
		$this->assertNull($converter->bool(1));
		$this->assertNull($converter->bool(0));
	}

	public function testBoolWithStringAndIntegerSameValueInTrueValues(): void
	{
		$converter = new LenientBoolConverter(trueValues: ['1', 1]);

		$this->assertTrue($converter->bool('1'));
		$this->assertTrue($converter->bool(1));
	}

	public function testBoolWithStringAndIntegerSameValueInFalseValues(): void
	{
		$converter = new LenientBoolConverter(falseValues: ['0', 0]);

		$this->assertFalse($converter->bool('0'));
		$this->assertFalse($converter->bool(0));
	}

	public function testBoolWithOnlyIntegerValues(): void
	{
		$converter = new LenientBoolConverter(
			trueValues: [1],
			falseValues: [0],
		);

		$this->assertTrue($converter->bool(1));
		$this->assertFalse($converter->bool(0));

		$this->assertNull($converter->bool('1'));
		$this->assertNull($converter->bool('0'));
		$this->assertNull($converter->bool('true'));
		$this->assertNull($converter->bool('false'));
	}

	public function testBoolWithOnlyStringValues(): void
	{
		$converter = new LenientBoolConverter(
			trueValues: ['yes', 'on'],
			falseValues: ['no', 'off'],
		);

		$this->assertTrue($converter->bool('yes'));
		$this->assertTrue($converter->bool('on'));
		$this->assertFalse($converter->bool('no'));
		$this->assertFalse($converter->bool('off'));

		$this->assertNull($converter->bool(1));
		$this->assertNull($converter->bool(0));
		$this->assertNull($converter->bool('true'));
		$this->assertNull($converter->bool('false'));
	}

}
