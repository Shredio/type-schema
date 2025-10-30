<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\Number;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\Number\NumberConverterHelper;

final class NumberConverterHelperTest extends TestCase
{

	public function testTryConvertToStrictIntWithPositiveDigits(): void
	{
		$this->assertSame(0, NumberConverterHelper::tryConvertToStrictInt('0'));
		$this->assertSame(1, NumberConverterHelper::tryConvertToStrictInt('1'));
		$this->assertSame(42, NumberConverterHelper::tryConvertToStrictInt('42'));
		$this->assertSame(123456, NumberConverterHelper::tryConvertToStrictInt('123456'));
		$this->assertSame(999999999, NumberConverterHelper::tryConvertToStrictInt('999999999'));
	}

	public function testTryConvertToStrictIntWithNegativeNumbers(): void
	{
		$this->assertSame(-1, NumberConverterHelper::tryConvertToStrictInt('-1'));
		$this->assertSame(-42, NumberConverterHelper::tryConvertToStrictInt('-42'));
		$this->assertSame(-123456, NumberConverterHelper::tryConvertToStrictInt('-123456'));
		$this->assertSame(-999999999, NumberConverterHelper::tryConvertToStrictInt('-999999999'));
	}

	public function testTryConvertToStrictIntWithLeadingZeros(): void
	{
		$this->assertSame(0, NumberConverterHelper::tryConvertToStrictInt('00'));
		$this->assertSame(1, NumberConverterHelper::tryConvertToStrictInt('01'));
		$this->assertSame(42, NumberConverterHelper::tryConvertToStrictInt('0042'));
		$this->assertSame(123, NumberConverterHelper::tryConvertToStrictInt('000123'));
	}

	public function testTryConvertToStrictIntWithNegativeLeadingZeros(): void
	{
		$this->assertSame(0, NumberConverterHelper::tryConvertToStrictInt('-0'));
		$this->assertSame(-1, NumberConverterHelper::tryConvertToStrictInt('-01'));
		$this->assertSame(-42, NumberConverterHelper::tryConvertToStrictInt('-0042'));
		$this->assertSame(-123, NumberConverterHelper::tryConvertToStrictInt('-000123'));
	}

	public function testTryConvertToStrictIntReturnsNullForInvalidFormats(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt(''));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt(' '));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('abc'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('12abc'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('abc12'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('12.34'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('12.0'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('-12.34'));
	}

	public function testTryConvertToStrictIntReturnsNullForWhitespace(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt(' 1'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('1 '));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt(' 1 '));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt("\t1"));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt("1\n"));
	}

	public function testTryConvertToStrictIntReturnsNullForSpecialCharacters(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('+1'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('1+'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('1-'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('--1'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('-'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('1e5'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('1E5'));
	}

	public function testTryConvertToStrictIntReturnsNullForMultipleMinusSigns(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('--1'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('1-2'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('-1-2'));
	}

	public function testTryConvertToStrictIntReturnsNullForHexadecimal(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('0x1A'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('0xFFFF'));
	}

	public function testTryConvertToStrictIntReturnsNullForOctal(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('0o17'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('0O17'));
	}

	public function testTryConvertToStrictIntReturnsNullForBinary(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('0b101'));
		$this->assertNull(NumberConverterHelper::tryConvertToStrictInt('0B101'));
	}

	public function testTryConvertToStrictIntWithLargeNumbers(): void
	{
		$this->assertSame(2147483647, NumberConverterHelper::tryConvertToStrictInt('2147483647'));
		$this->assertSame(-2147483648, NumberConverterHelper::tryConvertToStrictInt('-2147483648'));
		$this->assertSame(9223372036854775807, NumberConverterHelper::tryConvertToStrictInt('9223372036854775807'));
	}

	public function testTryConvertLenientFloatWithIntegers(): void
	{
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('0'));
		$this->assertSame(1.0, NumberConverterHelper::tryConvertLenientFloat('1'));
		$this->assertSame(42.0, NumberConverterHelper::tryConvertLenientFloat('42'));
		$this->assertSame(-1.0, NumberConverterHelper::tryConvertLenientFloat('-1'));
		$this->assertSame(-42.0, NumberConverterHelper::tryConvertLenientFloat('-42'));
	}

	public function testTryConvertLenientFloatWithPositiveSign(): void
	{
		$this->assertSame(1.0, NumberConverterHelper::tryConvertLenientFloat('+1'));
		$this->assertSame(42.0, NumberConverterHelper::tryConvertLenientFloat('+42'));
		$this->assertSame(3.14, NumberConverterHelper::tryConvertLenientFloat('+3.14'));
	}

	public function testTryConvertLenientFloatWithDecimals(): void
	{
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('0.0'));
		$this->assertSame(3.14, NumberConverterHelper::tryConvertLenientFloat('3.14'));
		$this->assertSame(-3.14, NumberConverterHelper::tryConvertLenientFloat('-3.14'));
		$this->assertSame(0.5, NumberConverterHelper::tryConvertLenientFloat('0.5'));
		$this->assertSame(-0.5, NumberConverterHelper::tryConvertLenientFloat('-0.5'));
		$this->assertSame(123.456789, NumberConverterHelper::tryConvertLenientFloat('123.456789'));
	}

	public function testTryConvertLenientFloatWithLeadingDecimalPoint(): void
	{
		$this->assertSame(0.5, NumberConverterHelper::tryConvertLenientFloat('.5'));
		$this->assertSame(0.123, NumberConverterHelper::tryConvertLenientFloat('.123'));
		$this->assertSame(-0.5, NumberConverterHelper::tryConvertLenientFloat('-.5'));
		$this->assertSame(0.5, NumberConverterHelper::tryConvertLenientFloat('+.5'));
	}

	public function testTryConvertLenientFloatWithTrailingDecimalPoint(): void
	{
		$this->assertSame(5.0, NumberConverterHelper::tryConvertLenientFloat('5.'));
		$this->assertSame(42.0, NumberConverterHelper::tryConvertLenientFloat('42.'));
		$this->assertSame(-5.0, NumberConverterHelper::tryConvertLenientFloat('-5.'));
		$this->assertSame(5.0, NumberConverterHelper::tryConvertLenientFloat('+5.'));
	}

	public function testTryConvertLenientFloatWithScientificNotation(): void
	{
		$this->assertSame(1e5, NumberConverterHelper::tryConvertLenientFloat('1e5'));
		$this->assertSame(1e5, NumberConverterHelper::tryConvertLenientFloat('1E5'));
		$this->assertSame(1e-5, NumberConverterHelper::tryConvertLenientFloat('1e-5'));
		$this->assertSame(1e-5, NumberConverterHelper::tryConvertLenientFloat('1E-5'));
		$this->assertSame(1e+5, NumberConverterHelper::tryConvertLenientFloat('1e+5'));
		$this->assertSame(1e+5, NumberConverterHelper::tryConvertLenientFloat('1E+5'));
	}

	public function testTryConvertLenientFloatWithScientificNotationAndDecimals(): void
	{
		$this->assertSame(3.14e5, NumberConverterHelper::tryConvertLenientFloat('3.14e5'));
		$this->assertSame(3.14e-5, NumberConverterHelper::tryConvertLenientFloat('3.14e-5'));
		$this->assertSame(-3.14e5, NumberConverterHelper::tryConvertLenientFloat('-3.14e5'));
		$this->assertSame(3.14e5, NumberConverterHelper::tryConvertLenientFloat('+3.14e5'));
	}

	public function testTryConvertLenientFloatWithScientificNotationLeadingDecimal(): void
	{
		$this->assertSame(.5e5, NumberConverterHelper::tryConvertLenientFloat('.5e5'));
		$this->assertSame(.5e-5, NumberConverterHelper::tryConvertLenientFloat('.5e-5'));
		$this->assertSame(.5e+5, NumberConverterHelper::tryConvertLenientFloat('.5e+5'));
	}

	public function testTryConvertLenientFloatWithLeadingZeros(): void
	{
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('00'));
		$this->assertSame(1.0, NumberConverterHelper::tryConvertLenientFloat('01'));
		$this->assertSame(42.0, NumberConverterHelper::tryConvertLenientFloat('0042'));
		$this->assertSame(3.14, NumberConverterHelper::tryConvertLenientFloat('003.14'));
		$this->assertSame(0.14, NumberConverterHelper::tryConvertLenientFloat('00.14'));
	}

	public function testTryConvertLenientFloatReturnsNullForInvalidFormats(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat(''));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat(' '));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('abc'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('12abc'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('abc12'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('12.34.56'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('..5'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('.'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('-.'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('+.'));
	}

	public function testTryConvertLenientFloatReturnsNullForWhitespace(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat(' 1'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('1 '));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat(' 1.5 '));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat("\t1.5"));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat("1.5\n"));
	}

	public function testTryConvertLenientFloatReturnsNullForMultipleSigns(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('++1'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('--1'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('+-1'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('-+1'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('1++'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('1--'));
	}

	public function testTryConvertLenientFloatReturnsNullForInvalidScientificNotation(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('e5'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('1e'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('1e+'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('1e-'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('1ee5'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('1e5e5'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('1e5.5'));
	}

	public function testTryConvertLenientFloatReturnsNullForHexadecimal(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('0x1A'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('0xFFFF'));
	}

	public function testTryConvertLenientFloatReturnsNullForOctal(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('0o17'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('0O17'));
	}

	public function testTryConvertLenientFloatReturnsNullForBinary(): void
	{
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('0b101'));
		$this->assertNull(NumberConverterHelper::tryConvertLenientFloat('0B101'));
	}

	public function testTryConvertLenientFloatWithLargeNumbers(): void
	{
		$this->assertSame(1.23e308, NumberConverterHelper::tryConvertLenientFloat('1.23e308'));
		$this->assertSame(-1.23e308, NumberConverterHelper::tryConvertLenientFloat('-1.23e308'));
		$this->assertSame(1.23e-308, NumberConverterHelper::tryConvertLenientFloat('1.23e-308'));
	}

	public function testTryConvertLenientFloatWithZeroVariations(): void
	{
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('0'));
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('-0'));
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('+0'));
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('0.0'));
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('-0.0'));
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('+0.0'));
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('.0'));
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('0.'));
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('0e0'));
		$this->assertSame(0.0, NumberConverterHelper::tryConvertLenientFloat('0.0e0'));
	}

}
