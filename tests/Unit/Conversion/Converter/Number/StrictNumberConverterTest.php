<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\Number;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\Number\StrictNumberConverter;

final class StrictNumberConverterTest extends TestCase
{

	private StrictNumberConverter $converter;

	protected function setUp(): void
	{
		$this->converter = new StrictNumberConverter();
	}

	public function testIntWithIntegers(): void
	{
		$this->assertSame(0, $this->converter->int(0));
		$this->assertSame(1, $this->converter->int(1));
		$this->assertSame(-1, $this->converter->int(-1));
		$this->assertSame(42, $this->converter->int(42));
		$this->assertSame(-42, $this->converter->int(-42));
		$this->assertSame(2147483647, $this->converter->int(2147483647));
		$this->assertSame(-2147483648, $this->converter->int(-2147483648));
		$this->assertSame(9223372036854775807, $this->converter->int(9223372036854775807));
	}

	public function testIntReturnsNullForFloats(): void
	{
		$this->assertNull($this->converter->int(0.0));
		$this->assertNull($this->converter->int(1.0));
		$this->assertNull($this->converter->int(3.14));
		$this->assertNull($this->converter->int(-3.14));
		$this->assertNull($this->converter->int(1.5));
		$this->assertNull($this->converter->int(42.0));
	}

	public function testIntReturnsNullForSpecialFloatValues(): void
	{
		$this->assertNull($this->converter->int(INF));
		$this->assertNull($this->converter->int(-INF));
		$this->assertNull($this->converter->int(NAN));
	}

	public function testIntReturnsNullForStrings(): void
	{
		$this->assertNull($this->converter->int('0'));
		$this->assertNull($this->converter->int('1'));
		$this->assertNull($this->converter->int('42'));
		$this->assertNull($this->converter->int('3.14'));
		$this->assertNull($this->converter->int(''));
	}

	public function testIntReturnsNullForBooleans(): void
	{
		$this->assertNull($this->converter->int(true));
		$this->assertNull($this->converter->int(false));
	}

	public function testIntReturnsNullForNull(): void
	{
		$this->assertNull($this->converter->int(null));
	}

	public function testIntReturnsNullForArrays(): void
	{
		$this->assertNull($this->converter->int([]));
		$this->assertNull($this->converter->int([1, 2, 3]));
	}

	public function testIntReturnsNullForObjects(): void
	{
		$this->assertNull($this->converter->int(new \stdClass()));
	}

	public function testFloatWithFloats(): void
	{
		$this->assertSame(0.0, $this->converter->float(0.0));
		$this->assertSame(1.0, $this->converter->float(1.0));
		$this->assertSame(3.14, $this->converter->float(3.14));
		$this->assertSame(-3.14, $this->converter->float(-3.14));
		$this->assertSame(1.5, $this->converter->float(1.5));
	}

	public function testFloatWithSpecialValues(): void
	{
		$this->assertSame(INF, $this->converter->float(INF));
		$this->assertNan($this->converter->float(NAN));
		$this->assertSame(-INF, $this->converter->float(-INF));
	}

	public function testFloatWithScientificNotation(): void
	{
		$this->assertSame(1e5, $this->converter->float(1e5));
		$this->assertSame(1e-5, $this->converter->float(1e-5));
		$this->assertSame(1.23e10, $this->converter->float(1.23e10));
	}

	public function testFloatReturnsNullForIntegers(): void
	{
		$this->assertNull($this->converter->float(0));
		$this->assertNull($this->converter->float(1));
		$this->assertNull($this->converter->float(-1));
		$this->assertNull($this->converter->float(42));
		$this->assertNull($this->converter->float(-42));
	}

	public function testFloatReturnsNullForStrings(): void
	{
		$this->assertNull($this->converter->float('0'));
		$this->assertNull($this->converter->float('1'));
		$this->assertNull($this->converter->float('3.14'));
		$this->assertNull($this->converter->float(''));
	}

	public function testFloatReturnsNullForBooleans(): void
	{
		$this->assertNull($this->converter->float(true));
		$this->assertNull($this->converter->float(false));
	}

	public function testFloatReturnsNullForNull(): void
	{
		$this->assertNull($this->converter->float(null));
	}

	public function testFloatReturnsNullForArrays(): void
	{
		$this->assertNull($this->converter->float([]));
		$this->assertNull($this->converter->float([1.5, 2.5]));
	}

	public function testFloatReturnsNullForObjects(): void
	{
		$this->assertNull($this->converter->float(new \stdClass()));
	}

	public function testIntWithZero(): void
	{
		$this->assertSame(0, $this->converter->int(0));
		$this->assertNull($this->converter->int(0.0));
	}

	public function testFloatWithZero(): void
	{
		$this->assertSame(0.0, $this->converter->float(0.0));
		$this->assertNull($this->converter->float(0));
	}

	public function testFloatWithNegativeZero(): void
	{
		$result = $this->converter->float(-0.0);
		$this->assertSame(0.0, $result);
	}

	public function testIntWithLargeIntegers(): void
	{
		$largeInt = 9223372036854775807;
		$this->assertSame($largeInt, $this->converter->int($largeInt));
	}

	public function testFloatWithVerySmallNumbers(): void
	{
		$this->assertSame(1e-308, $this->converter->float(1e-308));
	}

	public function testFloatWithVeryLargeNumbers(): void
	{
		$this->assertSame(1e308, $this->converter->float(1e308));
	}

}
