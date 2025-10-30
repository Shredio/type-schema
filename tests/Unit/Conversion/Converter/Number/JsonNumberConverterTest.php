<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\Number;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\Number\JsonNumberConverter;

final class JsonNumberConverterTest extends TestCase
{

	public function testIntWithIntegers(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertSame(0, $converter->int(0));
		$this->assertSame(1, $converter->int(1));
		$this->assertSame(-1, $converter->int(-1));
		$this->assertSame(42, $converter->int(42));
		$this->assertSame(-42, $converter->int(-42));
		$this->assertSame(2147483647, $converter->int(2147483647));
		$this->assertSame(-2147483648, $converter->int(-2147483648));
	}

	public function testIntReturnsNullForFloatsWithoutConversion(): void
	{
		$converter = new JsonNumberConverter(convertFloatToInt: false);

		$this->assertNull($converter->int(1.0));
		$this->assertNull($converter->int(42.0));
		$this->assertNull($converter->int(3.14));
		$this->assertNull($converter->int(-3.14));
		$this->assertNull($converter->int(1.5));
	}

	public function testIntConvertsFloatsWhenEnabled(): void
	{
		$converter = new JsonNumberConverter(convertFloatToInt: true);

		$this->assertSame(1, $converter->int(1.0));
		$this->assertSame(42, $converter->int(42.0));
		$this->assertSame(-42, $converter->int(-42.0));
		$this->assertSame(0, $converter->int(0.0));
	}

	public function testIntConvertsFloatsWithinEpsilon(): void
	{
		$converter = new JsonNumberConverter(convertFloatToInt: true, convertFloatToIntEpsilon: 1e-6);

		$this->assertSame(1, $converter->int(1.0000001));
		$this->assertSame(0, $converter->int(0.9999999));
		$this->assertSame(42, $converter->int(42.00000001));
		$this->assertSame(41, $converter->int(41.99999999));
	}

	public function testIntReturnsNullForFloatsOutsideEpsilon(): void
	{
		$converter = new JsonNumberConverter(convertFloatToInt: true, convertFloatToIntEpsilon: 1e-7);

		$this->assertNull($converter->int(1.1));
		$this->assertNull($converter->int(1.01));
		$this->assertNull($converter->int(1.001));
		$this->assertNull($converter->int(3.14));
		$this->assertNull($converter->int(42.5));
	}

	public function testIntWithCustomEpsilon(): void
	{
		$converter = new JsonNumberConverter(convertFloatToInt: true, convertFloatToIntEpsilon: 0.1);

		$this->assertSame(1, $converter->int(1.05));
		$this->assertSame(0, $converter->int(0.95));
		$this->assertNull($converter->int(1.2));
		$this->assertNull($converter->int(0.8));
	}

	public function testIntWithVerySmallEpsilon(): void
	{
		$converter = new JsonNumberConverter(convertFloatToInt: true, convertFloatToIntEpsilon: 1e-9);

		$this->assertSame(1, $converter->int(1.0000000001));
		$this->assertNull($converter->int(1.000001));
	}

	public function testIntReturnsNullForStrings(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->int('0'));
		$this->assertNull($converter->int('1'));
		$this->assertNull($converter->int('42'));
		$this->assertNull($converter->int('3.14'));
	}

	public function testIntReturnsNullForBooleans(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->int(true));
		$this->assertNull($converter->int(false));
	}

	public function testIntReturnsNullForNull(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->int(null));
	}

	public function testIntReturnsNullForArrays(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->int([]));
		$this->assertNull($converter->int([1, 2, 3]));
	}

	public function testIntReturnsNullForObjects(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->int(new \stdClass()));
	}

	public function testFloatWithFloats(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertSame(0.0, $converter->float(0.0));
		$this->assertSame(1.0, $converter->float(1.0));
		$this->assertSame(3.14, $converter->float(3.14));
		$this->assertSame(-3.14, $converter->float(-3.14));
		$this->assertSame(1.5, $converter->float(1.5));
	}

	public function testFloatWithIntegers(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertSame(0.0, $converter->float(0));
		$this->assertSame(1.0, $converter->float(1));
		$this->assertSame(-1.0, $converter->float(-1));
		$this->assertSame(42.0, $converter->float(42));
		$this->assertSame(-42.0, $converter->float(-42));
	}

	public function testFloatWithSpecialValues(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertSame(INF, $converter->float(INF));
		$this->assertNan($converter->float(NAN));
		$this->assertSame(-INF, $converter->float(-INF));
	}

	public function testFloatWithScientificNotation(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertSame(1e5, $converter->float(1e5));
		$this->assertSame(1e-5, $converter->float(1e-5));
		$this->assertSame(1.23e10, $converter->float(1.23e10));
	}

	public function testFloatReturnsNullForStrings(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->float('0'));
		$this->assertNull($converter->float('1'));
		$this->assertNull($converter->float('3.14'));
	}

	public function testFloatReturnsNullForBooleans(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->float(true));
		$this->assertNull($converter->float(false));
	}

	public function testFloatReturnsNullForNull(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->float(null));
	}

	public function testFloatReturnsNullForArrays(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->float([]));
		$this->assertNull($converter->float([1.5, 2.5]));
	}

	public function testFloatReturnsNullForObjects(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->float(new \stdClass()));
	}

	public function testDefaultConstructorParameters(): void
	{
		$converter = new JsonNumberConverter();

		$this->assertNull($converter->int(1.0));
		$this->assertSame(1, $converter->int(1));
	}

	public function testIntWithNegativeZeroFloat(): void
	{
		$converter = new JsonNumberConverter(convertFloatToInt: true);

		$this->assertSame(0, $converter->int(-0.0));
	}

	public function testFloatWithNegativeZero(): void
	{
		$converter = new JsonNumberConverter();

		$result = $converter->float(-0.0);
		$this->assertSame(0.0, $result);
	}

	public function testIntConvertsLargeFloats(): void
	{
		$converter = new JsonNumberConverter(convertFloatToInt: true);

		$this->assertSame(2147483647, $converter->int(2147483647.0));
		$this->assertSame(-2147483648, $converter->int(-2147483648.0));
	}

	public function testIntWithFloatTruncationEdgeCases(): void
	{
		$converter = new JsonNumberConverter(convertFloatToInt: true, convertFloatToIntEpsilon: 1e-7);

		$this->assertSame(99, $converter->int(99.99999999));
		$this->assertSame(100, $converter->int(100.00000001));
		$this->assertSame(0, $converter->int(0.00000001));
		$this->assertSame(0, $converter->int(-0.00000001));
	}

}
