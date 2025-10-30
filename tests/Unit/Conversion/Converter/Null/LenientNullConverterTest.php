<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\Null;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\Null\LenientNullConverter;

final class LenientNullConverterTest extends TestCase
{

	private LenientNullConverter $converter;

	protected function setUp(): void
	{
		$this->converter = new LenientNullConverter();
	}

	public function testNullWithNull(): void
	{
		$this->assertNull($this->converter->null(null));
	}

	public function testNullWithEmptyString(): void
	{
		$this->assertNull($this->converter->null(''));
	}

	public function testNullReturnsFalseForNonEmptyStrings(): void
	{
		$this->assertFalse($this->converter->null('null'));
		$this->assertFalse($this->converter->null('NULL'));
		$this->assertFalse($this->converter->null('Null'));
		$this->assertFalse($this->converter->null('0'));
		$this->assertFalse($this->converter->null('false'));
		$this->assertFalse($this->converter->null('hello'));
	}

	public function testNullReturnsFalseForWhitespace(): void
	{
		$this->assertFalse($this->converter->null(' '));
		$this->assertFalse($this->converter->null("\t"));
		$this->assertFalse($this->converter->null("\n"));
		$this->assertFalse($this->converter->null("\r"));
		$this->assertFalse($this->converter->null('   '));
		$this->assertFalse($this->converter->null("\t\n\r"));
	}

	public function testNullReturnsFalseForIntegers(): void
	{
		$this->assertFalse($this->converter->null(0));
		$this->assertFalse($this->converter->null(1));
		$this->assertFalse($this->converter->null(-1));
		$this->assertFalse($this->converter->null(42));
	}

	public function testNullReturnsFalseForFloats(): void
	{
		$this->assertFalse($this->converter->null(0.0));
		$this->assertFalse($this->converter->null(1.0));
		$this->assertFalse($this->converter->null(3.14));
		$this->assertFalse($this->converter->null(-3.14));
	}

	public function testNullReturnsFalseForBooleans(): void
	{
		$this->assertFalse($this->converter->null(true));
		$this->assertFalse($this->converter->null(false));
	}

	public function testNullReturnsFalseForArrays(): void
	{
		$this->assertFalse($this->converter->null([]));
		$this->assertFalse($this->converter->null([null]));
		$this->assertFalse($this->converter->null(['']));
		$this->assertFalse($this->converter->null([1, 2, 3]));
		$this->assertFalse($this->converter->null(['key' => 'value']));
	}

	public function testNullReturnsFalseForObjects(): void
	{
		$this->assertFalse($this->converter->null(new \stdClass()));
	}

	public function testNullWithEmptyStringVariations(): void
	{
		$this->assertNull($this->converter->null(''));

		$emptyString = '';
		$this->assertNull($this->converter->null($emptyString));

		$concatenated = 'test';
		$concatenated = substr($concatenated, 4);
		$this->assertNull($this->converter->null($concatenated));
	}

	public function testNullDistinguishesBetweenEmptyStringAndWhitespace(): void
	{
		$this->assertNull($this->converter->null(''));

		$this->assertFalse($this->converter->null(' '));
		$this->assertFalse($this->converter->null('  '));
		$this->assertFalse($this->converter->null("\t"));
	}

	public function testNullWithZeroValues(): void
	{
		$this->assertFalse($this->converter->null(0));
		$this->assertFalse($this->converter->null(0.0));
		$this->assertFalse($this->converter->null('0'));
		$this->assertFalse($this->converter->null(false));
	}

}
