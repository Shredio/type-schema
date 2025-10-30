<?php declare(strict_types = 1);

namespace Tests\Unit\Conversion\Converter\Bool;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\Converter\Bool\StrictBoolConverter;

final class StrictBoolConverterTest extends TestCase
{

	private StrictBoolConverter $converter;

	protected function setUp(): void
	{
		$this->converter = new StrictBoolConverter();
	}

	public function testBoolWithTrue(): void
	{
		$this->assertTrue($this->converter->bool(true));
	}

	public function testBoolWithFalse(): void
	{
		$this->assertFalse($this->converter->bool(false));
	}

	public function testBoolReturnsNullForStrings(): void
	{
		$this->assertNull($this->converter->bool('true'));
		$this->assertNull($this->converter->bool('false'));
		$this->assertNull($this->converter->bool('1'));
		$this->assertNull($this->converter->bool('0'));
		$this->assertNull($this->converter->bool('yes'));
		$this->assertNull($this->converter->bool('no'));
		$this->assertNull($this->converter->bool(''));
	}

	public function testBoolReturnsNullForIntegers(): void
	{
		$this->assertNull($this->converter->bool(1));
		$this->assertNull($this->converter->bool(0));
		$this->assertNull($this->converter->bool(-1));
		$this->assertNull($this->converter->bool(42));
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
		$this->assertNull($this->converter->bool([1, 2, 3]));
	}

	public function testBoolReturnsNullForObjects(): void
	{
		$this->assertNull($this->converter->bool(new \stdClass()));
	}

	public function testBoolReturnsNullForNull(): void
	{
		$this->assertNull($this->converter->bool(null));
	}

}
