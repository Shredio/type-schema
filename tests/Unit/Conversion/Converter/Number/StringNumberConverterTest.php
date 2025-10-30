<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\Number;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\Number\StringNumberConverter;

final class StringNumberConverterTest extends TestCase
{

	private StringNumberConverter $converter;

	protected function setUp(): void
	{
		$this->converter = new StringNumberConverter();
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
	}

	public function testIntWithPositiveIntegerStrings(): void
	{
		$this->assertSame(0, $this->converter->int('0'));
		$this->assertSame(1, $this->converter->int('1'));
		$this->assertSame(42, $this->converter->int('42'));
		$this->assertSame(123456, $this->converter->int('123456'));
	}

	public function testIntWithNegativeIntegerStrings(): void
	{
		$this->assertSame(-1, $this->converter->int('-1'));
		$this->assertSame(-42, $this->converter->int('-42'));
		$this->assertSame(-123456, $this->converter->int('-123456'));
	}

	public function testIntWithLeadingZeros(): void
	{
		$this->assertSame(0, $this->converter->int('00'));
		$this->assertSame(1, $this->converter->int('01'));
		$this->assertSame(42, $this->converter->int('0042'));
		$this->assertSame(-1, $this->converter->int('-01'));
		$this->assertSame(-42, $this->converter->int('-0042'));
	}

	public function testIntReturnsNullForFloats(): void
	{
		$this->assertNull($this->converter->int(0.0));
		$this->assertNull($this->converter->int(1.0));
		$this->assertNull($this->converter->int(3.14));
		$this->assertNull($this->converter->int(-3.14));
	}

	public function testIntReturnsNullForFloatStrings(): void
	{
		$this->assertNull($this->converter->int('1.0'));
		$this->assertNull($this->converter->int('3.14'));
		$this->assertNull($this->converter->int('-3.14'));
		$this->assertNull($this->converter->int('0.0'));
	}

	public function testIntReturnsNullForInvalidStrings(): void
	{
		$this->assertNull($this->converter->int(''));
		$this->assertNull($this->converter->int('abc'));
		$this->assertNull($this->converter->int('12abc'));
		$this->assertNull($this->converter->int('abc12'));
		$this->assertNull($this->converter->int('1e5'));
	}

	public function testIntReturnsNullForStringsWithWhitespace(): void
	{
		$this->assertNull($this->converter->int(' 1'));
		$this->assertNull($this->converter->int('1 '));
		$this->assertNull($this->converter->int(' 1 '));
		$this->assertNull($this->converter->int("\t1"));
		$this->assertNull($this->converter->int("1\n"));
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
		$this->assertNull($this->converter->int(['1']));
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
	}

	public function testFloatWithPositiveIntegerStrings(): void
	{
		$this->assertSame(0.0, $this->converter->float('0'));
		$this->assertSame(1.0, $this->converter->float('1'));
		$this->assertSame(42.0, $this->converter->float('42'));
		$this->assertSame(123456.0, $this->converter->float('123456'));
	}

	public function testFloatWithNegativeIntegerStrings(): void
	{
		$this->assertSame(-1.0, $this->converter->float('-1'));
		$this->assertSame(-42.0, $this->converter->float('-42'));
		$this->assertSame(-123456.0, $this->converter->float('-123456'));
	}

	public function testFloatWithFloatStrings(): void
	{
		$this->assertSame(0.0, $this->converter->float('0.0'));
		$this->assertSame(1.0, $this->converter->float('1.0'));
		$this->assertSame(3.14, $this->converter->float('3.14'));
		$this->assertSame(-3.14, $this->converter->float('-3.14'));
		$this->assertSame(0.5, $this->converter->float('0.5'));
	}

	public function testFloatWithLeadingDecimalPoint(): void
	{
		$this->assertSame(0.5, $this->converter->float('.5'));
		$this->assertSame(0.123, $this->converter->float('.123'));
		$this->assertSame(-0.5, $this->converter->float('-.5'));
		$this->assertSame(0.5, $this->converter->float('+.5'));
	}

	public function testFloatWithTrailingDecimalPoint(): void
	{
		$this->assertSame(5.0, $this->converter->float('5.'));
		$this->assertSame(42.0, $this->converter->float('42.'));
		$this->assertSame(-5.0, $this->converter->float('-5.'));
		$this->assertSame(5.0, $this->converter->float('+5.'));
	}

	public function testFloatWithScientificNotation(): void
	{
		$this->assertSame(1e5, $this->converter->float('1e5'));
		$this->assertSame(1e5, $this->converter->float('1E5'));
		$this->assertSame(1e-5, $this->converter->float('1e-5'));
		$this->assertSame(1e+5, $this->converter->float('1e+5'));
		$this->assertSame(3.14e5, $this->converter->float('3.14e5'));
	}

	public function testFloatWithPositiveSign(): void
	{
		$this->assertSame(1.0, $this->converter->float('+1'));
		$this->assertSame(42.0, $this->converter->float('+42'));
		$this->assertSame(3.14, $this->converter->float('+3.14'));
	}

	public function testFloatWithLeadingZeros(): void
	{
		$this->assertSame(0.0, $this->converter->float('00'));
		$this->assertSame(1.0, $this->converter->float('01'));
		$this->assertSame(42.0, $this->converter->float('0042'));
		$this->assertSame(3.14, $this->converter->float('003.14'));
	}

	public function testFloatReturnsNullForEmptyString(): void
	{
		$this->assertNull($this->converter->float(''));
	}

	public function testFloatReturnsNullForInvalidStrings(): void
	{
		$this->assertNull($this->converter->float('abc'));
		$this->assertNull($this->converter->float('12abc'));
		$this->assertNull($this->converter->float('abc12'));
		$this->assertNull($this->converter->float('12.34.56'));
		$this->assertNull($this->converter->float('..5'));
		$this->assertNull($this->converter->float('.'));
	}

	public function testFloatReturnsNullForStringsWithWhitespace(): void
	{
		$this->assertNull($this->converter->float(' 1'));
		$this->assertNull($this->converter->float('1 '));
		$this->assertNull($this->converter->float(' 1.5 '));
		$this->assertNull($this->converter->float("\t1.5"));
		$this->assertNull($this->converter->float("1.5\n"));
	}

	public function testFloatReturnsNullForIntegers(): void
	{
		$this->assertNull($this->converter->float(0));
		$this->assertNull($this->converter->float(1));
		$this->assertNull($this->converter->float(42));
		$this->assertNull($this->converter->float(-42));
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
		$this->assertNull($this->converter->float(['1.5']));
	}

	public function testFloatReturnsNullForObjects(): void
	{
		$this->assertNull($this->converter->float(new \stdClass()));
	}

	public function testIntWithLargeIntegerStrings(): void
	{
		$this->assertSame(2147483647, $this->converter->int('2147483647'));
		$this->assertSame(-2147483648, $this->converter->int('-2147483648'));
		$this->assertSame(9223372036854775807, $this->converter->int('9223372036854775807'));
	}

	public function testFloatWithSpecialFloatValues(): void
	{
		$this->assertSame(INF, $this->converter->float(INF));
		$this->assertNan($this->converter->float(NAN));
		$this->assertSame(-INF, $this->converter->float(-INF));
	}

	public function testIntWithZeroString(): void
	{
		$this->assertSame(0, $this->converter->int('0'));
		$this->assertSame(0, $this->converter->int('-0'));
	}

	public function testFloatWithZeroString(): void
	{
		$this->assertSame(0.0, $this->converter->float('0'));
		$this->assertSame(0.0, $this->converter->float('-0'));
		$this->assertSame(0.0, $this->converter->float('0.0'));
		$this->assertSame(0.0, $this->converter->float('-0.0'));
	}

}
