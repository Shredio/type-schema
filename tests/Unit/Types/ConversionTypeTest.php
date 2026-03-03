<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Conversion\Converter\Bool\LenientBoolConverter;
use Shredio\TypeSchema\Conversion\Converter\Null\LenientNullConverter;
use Shredio\TypeSchema\Conversion\Converter\Number\LenientNumberConverter;
use Shredio\TypeSchema\Conversion\Converter\String\LenientStringConverter;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\TypeSchema;
use Tests\TestCase;

final class ConversionTypeTest extends TestCase
{

	public function testStringConversionConvertsIntToString(): void
	{
		$type = TypeSchema::get()->string()->conversion(
			string: new LenientStringConverter(),
		);

		self::assertSame('42', $this->validStrictParse($type, 42));
	}

	public function testStringConversionConvertsFloatToString(): void
	{
		$type = TypeSchema::get()->string()->conversion(
			string: new LenientStringConverter(),
		);

		self::assertSame('3.14', $this->validStrictParse($type, 3.14));
	}

	public function testStringConversionDoesNotAffectValidString(): void
	{
		$type = TypeSchema::get()->string()->conversion(
			string: new LenientStringConverter(),
		);

		self::assertSame('hello', $this->validStrictParse($type, 'hello'));
	}

	public function testIntConversionConvertsStringToInt(): void
	{
		$type = TypeSchema::get()->int()->conversion(
			int: new LenientNumberConverter(),
		);

		self::assertSame(42, $this->validStrictParse($type, '42'));
	}

	public function testIntConversionConvertsFloatToInt(): void
	{
		$type = TypeSchema::get()->int()->conversion(
			int: new LenientNumberConverter(),
		);

		self::assertSame(5, $this->validStrictParse($type, 5.0));
	}

	public function testFloatConversionConvertsStringToFloat(): void
	{
		$type = TypeSchema::get()->float()->conversion(
			float: new LenientNumberConverter(),
		);

		self::assertSame(3.14, $this->validStrictParse($type, '3.14'));
	}

	public function testFloatConversionConvertsIntToFloat(): void
	{
		$type = TypeSchema::get()->float()->conversion(
			float: new LenientNumberConverter(),
		);

		self::assertSame(42.0, $this->validStrictParse($type, 42));
	}

	public function testBoolConversionConvertsStringToBool(): void
	{
		$type = TypeSchema::get()->bool()->conversion(
			bool: new LenientBoolConverter(),
		);

		self::assertTrue($this->validStrictParse($type, 'true'));
		self::assertFalse($this->validStrictParse($type, 'false'));
	}

	public function testBoolConversionConvertsIntToBool(): void
	{
		$type = TypeSchema::get()->bool()->conversion(
			bool: new LenientBoolConverter(),
		);

		self::assertTrue($this->validStrictParse($type, 1));
		self::assertFalse($this->validStrictParse($type, 0));
	}

	public function testNullConversionConvertsEmptyStringToNull(): void
	{
		$type = TypeSchema::get()->null()->conversion(
			null: new LenientNullConverter(),
		);

		self::assertNull($this->validStrictParse($type, ''));
	}

	public function testConversionDoesNotAffectOtherTypes(): void
	{
		$type = TypeSchema::get()->int()->conversion(
			string: new LenientStringConverter(),
		);

		$result = $this->getProcessor()->parse('42', $type);
		self::assertInstanceOf(ErrorElement::class, $result);
	}

	public function testConversionOverridesGlobalStrategy(): void
	{
		$type = TypeSchema::get()->string()->conversion(
			string: new LenientStringConverter(),
		);

		$result = $this->getProcessor()->parse(42, $type, new TypeConfig(ConversionStrategyFactory::strict()));
		self::assertNotInstanceOf(ErrorElement::class, $result);
		self::assertSame('42', $result);
	}

	public function testMultipleConversionsAtOnce(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape([
			'name' => $t->string()->conversion(string: new LenientStringConverter()),
			'age' => $t->int()->conversion(int: new LenientNumberConverter()),
			'active' => $t->bool()->conversion(bool: new LenientBoolConverter()),
		]);

		$result = $this->validStrictParse($type, [
			'name' => 123,
			'age' => '25',
			'active' => 'true',
		]);

		self::assertSame('123', $result['name']);
		self::assertSame(25, $result['age']);
		self::assertTrue($result['active']);
	}

	public function testConversionWithNullParametersKeepsDefaults(): void
	{
		$type = TypeSchema::get()->int()->conversion();

		$result = $this->getProcessor()->parse(42, $type);
		self::assertNotInstanceOf(ErrorElement::class, $result);
		self::assertSame(42, $result);
	}

}