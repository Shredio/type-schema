<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\String;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\String\LenientStringConverter;

final class LenientStringConverterTest extends TestCase
{

	private LenientStringConverter $converter;

	protected function setUp(): void
	{
		$this->converter = new LenientStringConverter();
	}

	public function testStringWithEmptyString(): void
	{
		$this->assertSame('', $this->converter->string(''));
	}

	public function testStringWithSimpleStrings(): void
	{
		$this->assertSame('hello', $this->converter->string('hello'));
		$this->assertSame('world', $this->converter->string('world'));
		$this->assertSame('test', $this->converter->string('test'));
	}

	public function testStringWithNumericStrings(): void
	{
		$this->assertSame('0', $this->converter->string('0'));
		$this->assertSame('1', $this->converter->string('1'));
		$this->assertSame('42', $this->converter->string('42'));
		$this->assertSame('-42', $this->converter->string('-42'));
		$this->assertSame('3.14', $this->converter->string('3.14'));
		$this->assertSame('1e5', $this->converter->string('1e5'));
	}

	public function testStringWithWhitespace(): void
	{
		$this->assertSame(' ', $this->converter->string(' '));
		$this->assertSame('  ', $this->converter->string('  '));
		$this->assertSame("\t", $this->converter->string("\t"));
		$this->assertSame("\n", $this->converter->string("\n"));
		$this->assertSame("\r", $this->converter->string("\r"));
		$this->assertSame(" \t\n\r ", $this->converter->string(" \t\n\r "));
	}

	public function testStringWithSpecialCharacters(): void
	{
		$this->assertSame('hello world', $this->converter->string('hello world'));
		$this->assertSame('hello@world.com', $this->converter->string('hello@world.com'));
		$this->assertSame('!@#$%^&*()', $this->converter->string('!@#$%^&*()'));
		$this->assertSame('こんにちは', $this->converter->string('こんにちは'));
		$this->assertSame('🎉', $this->converter->string('🎉'));
	}

	public function testStringWithIntegers(): void
	{
		$this->assertSame('0', $this->converter->string(0));
		$this->assertSame('1', $this->converter->string(1));
		$this->assertSame('-1', $this->converter->string(-1));
		$this->assertSame('42', $this->converter->string(42));
		$this->assertSame('-42', $this->converter->string(-42));
		$this->assertSame('2147483647', $this->converter->string(2147483647));
		$this->assertSame('-2147483648', $this->converter->string(-2147483648));
	}

	public function testStringWithFloats(): void
	{
		$this->assertSame('0', $this->converter->string(0.0));
		$this->assertSame('1', $this->converter->string(1.0));
		$this->assertSame('3.14', $this->converter->string(3.14));
		$this->assertSame('-3.14', $this->converter->string(-3.14));
	}

	public function testStringWithFloatsScientificNotation(): void
	{
		$result = $this->converter->string(1e5);
		$this->assertIsString($result);
		$this->assertSame('100000', $result);

		$result = $this->converter->string(1e-5);
		$this->assertIsString($result);
		$this->assertSame('1.0E-5', $result);

		$result = $this->converter->string(1.23e10);
		$this->assertIsString($result);
		$this->assertSame('12300000000', $result);
	}

	public function testStringWithSpecialFloatValues(): void
	{
		$this->assertSame('INF', $this->converter->string(INF));
		$this->assertSame('-INF', $this->converter->string(-INF));
		$this->assertSame('NAN', $this->converter->string(NAN));
	}

	public function testStringReturnsNullForBooleans(): void
	{
		$this->assertNull($this->converter->string(true));
		$this->assertNull($this->converter->string(false));
	}

	public function testStringReturnsNullForNull(): void
	{
		$this->assertNull($this->converter->string(null));
	}

	public function testStringReturnsNullForArrays(): void
	{
		$this->assertNull($this->converter->string([]));
		$this->assertNull($this->converter->string([1, 2, 3]));
		$this->assertNull($this->converter->string(['key' => 'value']));
		$this->assertNull($this->converter->string(['hello']));
	}

	public function testStringReturnsNullForObjects(): void
	{
		$this->assertNull($this->converter->string(new \stdClass()));
	}

	public function testStringWithLongStrings(): void
	{
		$longString = str_repeat('a', 10000);
		$this->assertSame($longString, $this->converter->string($longString));
	}

	public function testStringWithIntegerZero(): void
	{
		$this->assertSame('0', $this->converter->string(0));
		$this->assertNotSame('', $this->converter->string(0));
	}

	public function testStringWithFloatZero(): void
	{
		$this->assertSame('0', $this->converter->string(0.0));
		$this->assertNotSame('', $this->converter->string(0.0));
	}

	public function testStringConversionProducesEqualResults(): void
	{
		$this->assertSame('42', $this->converter->string('42'));
		$this->assertSame('42', $this->converter->string(42));
		$this->assertSame($this->converter->string('42'), $this->converter->string(42));
	}

	public function testStringWithLargeIntegers(): void
	{
		$largeInt = 9223372036854775807;
		$this->assertSame('9223372036854775807', $this->converter->string($largeInt));
	}

	public function testStringWithNegativeZero(): void
	{
		$this->assertSame('-0', $this->converter->string(-0.0));
	}

	public function testStringWithVerySmallFloat(): void
	{
		$result = $this->converter->string(0.0000001);
		$this->assertIsString($result);
		$this->assertSame('1.0E-7', $result);
	}

	public function testStringWithVeryLargeFloat(): void
	{
		$result = $this->converter->string(1.23e308);
		$this->assertIsString($result);
		$this->assertStringContainsString('E+', $result);
	}

}
