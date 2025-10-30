<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\String;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\String\StrictStringConverter;

final class StrictStringConverterTest extends TestCase
{

	private StrictStringConverter $converter;

	protected function setUp(): void
	{
		$this->converter = new StrictStringConverter();
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

	public function testStringWithMultilineStrings(): void
	{
		$multiline = "line1\nline2\nline3";
		$this->assertSame($multiline, $this->converter->string($multiline));

		$multiline = "line1\r\nline2\r\nline3";
		$this->assertSame($multiline, $this->converter->string($multiline));
	}

	public function testStringReturnsNullForIntegers(): void
	{
		$this->assertNull($this->converter->string(0));
		$this->assertNull($this->converter->string(1));
		$this->assertNull($this->converter->string(-1));
		$this->assertNull($this->converter->string(42));
		$this->assertNull($this->converter->string(-42));
	}

	public function testStringReturnsNullForFloats(): void
	{
		$this->assertNull($this->converter->string(0.0));
		$this->assertNull($this->converter->string(1.0));
		$this->assertNull($this->converter->string(3.14));
		$this->assertNull($this->converter->string(-3.14));
		$this->assertNull($this->converter->string(1e5));
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

}
