<?php declare(strict_types = 1);

namespace Tests\Unit\Mapper;

use Shredio\TypeSchema\Mapper\BackedEnumMapper;
use Tests\TypeTestCase;

final class BackedEnumMapperTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new BackedEnumMapper(TestBackedStringEnum::class));
		yield 'string enum first value' => TestBackedStringEnum::First;
		yield 'string enum second value' => TestBackedStringEnum::Second;

		yield $this->typeToTest(new BackedEnumMapper(TestBackedIntEnum::class));
		yield 'int enum first value' => TestBackedIntEnum::Alpha;
		yield 'int enum second value' => TestBackedIntEnum::Beta;
	}

	protected function getInvalidValues(): iterable
	{
		yield $this->typeToTest(new BackedEnumMapper(TestBackedStringEnum::class), ['backedEnum']);
		yield 'string backing value first' => 'first';
		yield 'string backing value second' => 'second';
		yield 'wrong string value' => 'invalid';
		yield 'integer for string enum' => 123;
		yield 'float' => 45.67;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;
		yield 'empty array' => [];
		yield 'stdClass object' => new \stdClass();
		yield 'different enum type' => TestBackedIntEnum::Alpha;

		yield $this->typeToTest(new BackedEnumMapper(TestBackedIntEnum::class), ['backedEnum']);
		yield 'wrong int value' => 999;
		yield 'string for int enum' => 'invalid';
		yield 'different enum type for int' => TestBackedStringEnum::First;
		yield 'int backing value first' => 1;
		yield 'int backing value second' => 2;
	}

}

enum TestBackedStringEnum: string
{
	case First = 'first';
	case Second = 'second';
}

enum TestBackedIntEnum: int
{
	case Alpha = 1;
	case Beta = 2;
}
