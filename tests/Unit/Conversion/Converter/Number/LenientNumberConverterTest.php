<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\Number;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\Number\LenientNumberConverter;

final class LenientNumberConverterTest extends TestCase
{

	public function testIntWithIntegers(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(0, $converter->int(0));
		$this->assertSame(1, $converter->int(1));
		$this->assertSame(-1, $converter->int(-1));
		$this->assertSame(42, $converter->int(42));
		$this->assertSame(-42, $converter->int(-42));
		$this->assertSame(2147483647, $converter->int(2147483647));
		$this->assertSame(-2147483648, $converter->int(-2147483648));
	}

	public function testIntWithFloatsExactValues(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(0, $converter->int(0.0));
		$this->assertSame(1, $converter->int(1.0));
		$this->assertSame(42, $converter->int(42.0));
		$this->assertSame(-42, $converter->int(-42.0));
		$this->assertSame(100, $converter->int(100.0));
	}

	public function testIntReturnsNullForFloatsWithFraction(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->int(1.5));
		$this->assertNull($converter->int(3.14));
		$this->assertNull($converter->int(-3.14));
		$this->assertNull($converter->int(0.1));
		$this->assertNull($converter->int(42.001));
	}

	public function testIntWithFloatPrecisionCheck(): void
	{
		$converter = new LenientNumberConverter(checkFloatPrecisionOnCastToInt: true);

		$this->assertSame(1, $converter->int(1.0));
		$this->assertNull($converter->int(1.5));
		$this->assertNull($converter->int(1.0000001));
	}

	public function testIntWithFloatPrecisionCheckDisabled(): void
	{
		$converter = new LenientNumberConverter(checkFloatPrecisionOnCastToInt: false);

		$this->assertSame(1, $converter->int(1.0));
		$this->assertSame(1, $converter->int(1.5));
		$this->assertSame(3, $converter->int(3.14));
		$this->assertSame(42, $converter->int(42.999));
		$this->assertSame(-3, $converter->int(-3.14));
	}

	public function testIntWithIntegerStrings(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(0, $converter->int('0'));
		$this->assertSame(1, $converter->int('1'));
		$this->assertSame(42, $converter->int('42'));
		$this->assertSame(-42, $converter->int('-42'));
	}

	public function testIntWithLeadingZeros(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(0, $converter->int('00'));
		$this->assertSame(1, $converter->int('01'));
		$this->assertSame(42, $converter->int('0042'));
		$this->assertSame(-1, $converter->int('-01'));
	}

	public function testIntReturnsNullForFloatStrings(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->int('1.0'));
		$this->assertNull($converter->int('3.14'));
		$this->assertNull($converter->int('-3.14'));
	}

	public function testIntReturnsNullForInvalidStrings(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->int(''));
		$this->assertNull($converter->int('abc'));
		$this->assertNull($converter->int('12abc'));
		$this->assertNull($converter->int('1e5'));
	}

	public function testIntReturnsNullForStringsWithWhitespace(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->int(' 1'));
		$this->assertNull($converter->int('1 '));
		$this->assertNull($converter->int(' 1 '));
	}

	public function testIntReturnsNullForBooleans(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->int(true));
		$this->assertNull($converter->int(false));
	}

	public function testIntReturnsNullForNull(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->int(null));
	}

	public function testIntReturnsNullForArrays(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->int([]));
		$this->assertNull($converter->int([1]));
	}

	public function testIntReturnsNullForObjects(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->int(new \stdClass()));
	}

	public function testFloatWithFloats(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(0.0, $converter->float(0.0));
		$this->assertSame(1.0, $converter->float(1.0));
		$this->assertSame(3.14, $converter->float(3.14));
		$this->assertSame(-3.14, $converter->float(-3.14));
	}

	public function testFloatWithIntegers(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(0.0, $converter->float(0));
		$this->assertSame(1.0, $converter->float(1));
		$this->assertSame(42.0, $converter->float(42));
		$this->assertSame(-42.0, $converter->float(-42));
	}

	public function testFloatWithIntegerStrings(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(0.0, $converter->float('0'));
		$this->assertSame(1.0, $converter->float('1'));
		$this->assertSame(42.0, $converter->float('42'));
		$this->assertSame(-42.0, $converter->float('-42'));
	}

	public function testFloatWithFloatStrings(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(0.0, $converter->float('0.0'));
		$this->assertSame(1.0, $converter->float('1.0'));
		$this->assertSame(3.14, $converter->float('3.14'));
		$this->assertSame(-3.14, $converter->float('-3.14'));
	}

	public function testFloatWithLeadingDecimalPoint(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(0.5, $converter->float('.5'));
		$this->assertSame(0.123, $converter->float('.123'));
		$this->assertSame(-0.5, $converter->float('-.5'));
	}

	public function testFloatWithTrailingDecimalPoint(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(5.0, $converter->float('5.'));
		$this->assertSame(42.0, $converter->float('42.'));
		$this->assertSame(-5.0, $converter->float('-5.'));
	}

	public function testFloatWithScientificNotation(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(1e5, $converter->float('1e5'));
		$this->assertSame(1e-5, $converter->float('1e-5'));
		$this->assertSame(3.14e5, $converter->float('3.14e5'));
	}

	public function testFloatWithPositiveSign(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(1.0, $converter->float('+1'));
		$this->assertSame(3.14, $converter->float('+3.14'));
	}

	public function testFloatReturnsNullForEmptyString(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->float(''));
	}

	public function testFloatReturnsNullForInvalidStrings(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->float('abc'));
		$this->assertNull($converter->float('12abc'));
		$this->assertNull($converter->float('..5'));
	}

	public function testFloatReturnsNullForStringsWithWhitespace(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->float(' 1'));
		$this->assertNull($converter->float('1 '));
		$this->assertNull($converter->float(' 3.14 '));
	}

	public function testFloatReturnsNullForBooleans(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->float(true));
		$this->assertNull($converter->float(false));
	}

	public function testFloatReturnsNullForNull(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->float(null));
	}

	public function testFloatReturnsNullForArrays(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->float([]));
		$this->assertNull($converter->float([1.5]));
	}

	public function testFloatReturnsNullForObjects(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertNull($converter->float(new \stdClass()));
	}

	public function testIntWithLargeFloats(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(2147483647, $converter->int(2147483647.0));
		$this->assertSame(-2147483648, $converter->int(-2147483648.0));
	}

	public function testFloatWithSpecialValues(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(INF, $converter->float(INF));
		$this->assertNan($converter->float(NAN));
		$this->assertSame(-INF, $converter->float(-INF));
	}

	public function testIntWithNegativeZeroFloat(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(0, $converter->int(-0.0));
	}

	public function testFloatWithNegativeZero(): void
	{
		$converter = new LenientNumberConverter();

		$result = $converter->float(-0.0);
		$this->assertSame(0.0, $result);
	}

	public function testIntTruncatesWhenPrecisionCheckDisabled(): void
	{
		$converter = new LenientNumberConverter(checkFloatPrecisionOnCastToInt: false);

		$this->assertSame(0, $converter->int(0.9));
		$this->assertSame(1, $converter->int(1.9));
		$this->assertSame(99, $converter->int(99.99));
		$this->assertSame(-1, $converter->int(-1.9));
	}

	public function testIntWithFloatEdgeCases(): void
	{
		$converter = new LenientNumberConverter(checkFloatPrecisionOnCastToInt: true);

		$this->assertSame(1, $converter->int(1.0));
		$this->assertNull($converter->int(1.0 + 1e-15));
	}

	public function testDefaultConstructorBehavior(): void
	{
		$converter = new LenientNumberConverter();

		$this->assertSame(1, $converter->int(1.0));
		$this->assertNull($converter->int(1.5));
	}

}
