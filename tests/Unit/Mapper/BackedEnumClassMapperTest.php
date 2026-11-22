<?php declare(strict_types = 1);

namespace Tests\Unit\Mapper;

use BackedEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Helper\EnumHelper;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\NotAllowedValue;
use Shredio\TypeSchema\Mapper\BackedEnumClassMapper;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use stdClass;
use Tests\Unit\Mapper\Fixtures\TestBackedIntEnum;
use Tests\Unit\Mapper\Fixtures\TestBackedStringEnum;
use Tests\Unit\Mapper\Fixtures\TestEmptyBackedEnum;
use Tests\Unit\Mapper\Fixtures\TestSingleCaseEnum;

final class BackedEnumClassMapperTest extends TestCase
{

	/**
	 * @return iterable<string, array{class-string<BackedEnum>, mixed, BackedEnum}>
	 */
	public static function provideValidValues(): iterable
	{
		yield 'string enum from backing value' => [TestBackedStringEnum::class, 'first', TestBackedStringEnum::First];
		yield 'string enum from second backing value' => [TestBackedStringEnum::class, 'second', TestBackedStringEnum::Second];
		yield 'string enum instance' => [TestBackedStringEnum::class, TestBackedStringEnum::Second, TestBackedStringEnum::Second];
		yield 'int enum from backing value' => [TestBackedIntEnum::class, 1, TestBackedIntEnum::Alpha];
		yield 'int enum from numeric string' => [TestBackedIntEnum::class, '2', TestBackedIntEnum::Beta];
		yield 'int enum instance' => [TestBackedIntEnum::class, TestBackedIntEnum::Alpha, TestBackedIntEnum::Alpha];
	}

	/**
	 * @param class-string<BackedEnum> $enumClass
	 */
	#[DataProvider('provideValidValues')]
	public function testLenientParsingCreatesEnumCase(string $enumClass, mixed $value, BackedEnum $expected): void
	{
		$result = $this->createLenientProcessor()->parse($value, TypeSchema::get()->mapper($enumClass));

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame($expected, $result->value);
	}

	public function testUnknownStringBackingValueIsNotAllowed(): void
	{
		$result = $this->createLenientProcessor()->parse('frist', TypeSchema::get()->mapper(TestBackedStringEnum::class));

		$this->assertInstanceOf(Failure::class, $result);
		$issue = $result->getReports()[0]->issue;
		$this->assertInstanceOf(NotAllowedValue::class, $issue);
		$this->assertSame('frist', $issue->value);
		$this->assertSame(['first', 'second'], $issue->allowedValues);
		$this->assertSame(
			'Value "frist" is not a valid choice, did you mean "first"? Possible values are "first" or "second".',
			$result->getReports()[0]->messageForDeveloper,
		);
	}

	public function testUnknownIntBackingValueIsNotAllowed(): void
	{
		$result = $this->createLenientProcessor()->parse(999, TypeSchema::get()->mapper(TestBackedIntEnum::class));

		$this->assertInstanceOf(Failure::class, $result);
		$issue = $result->getReports()[0]->issue;
		$this->assertInstanceOf(NotAllowedValue::class, $issue);
		$this->assertSame(999, $issue->value);
		$this->assertSame([1, 2], $issue->allowedValues);
	}

	/**
	 * @return iterable<string, array{class-string<BackedEnum>, mixed, string}>
	 */
	public static function provideValuesOfInvalidType(): iterable
	{
		yield 'array for string enum' => [TestBackedStringEnum::class, [], 'Invalid type array, expected string.'];
		yield 'object for string enum' => [TestBackedStringEnum::class, new stdClass(), 'Invalid type stdClass, expected string.'];
		yield 'other enum for string enum' => [TestBackedStringEnum::class, TestBackedIntEnum::Alpha, sprintf('Invalid type %s, expected string.', TestBackedIntEnum::class)];
		yield 'non-numeric string for int enum' => [TestBackedIntEnum::class, 'alpha', 'Invalid type string with value "alpha", expected int.'];
		yield 'float for int enum' => [TestBackedIntEnum::class, 1.5, 'Invalid type float with value 1.5, expected int.'];
		yield 'value for enum without cases' => [TestEmptyBackedEnum::class, 1, 'Invalid type int with value 1, expected int.'];
	}

	/**
	 * @param class-string<BackedEnum> $enumClass
	 */
	#[DataProvider('provideValuesOfInvalidType')]
	public function testValueOfInvalidTypeIsRejected(string $enumClass, mixed $value, string $expectedMessage): void
	{
		$result = $this->createLenientProcessor()->parse($value, TypeSchema::get()->mapper($enumClass));

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertInstanceOf(InvalidType::class, $result->getReports()[0]->issue);
		$this->assertSame($expectedMessage, $result->getReports()[0]->messageForDeveloper);
	}

	public function testStrictObjectStrategyAcceptsOnlyEnumInstances(): void
	{
		$processor = TypeSchemaProcessor::createDefault(ConversionStrategyFactory::strict());
		$type = TypeSchema::get()->mapper(TestBackedStringEnum::class);

		$this->assertSame(TestBackedStringEnum::First, $processor->process(TestBackedStringEnum::First, $type));

		$result = $processor->parse('first', $type);
		$this->assertInstanceOf(Failure::class, $result);
		$this->assertSame(
			sprintf('Invalid type string with value "first", expected %s.', TestBackedStringEnum::class),
			$result->getReports()[0]->messageForDeveloper,
		);
	}

	public function testSupportsOnlyBackedEnums(): void
	{
		$mapper = new BackedEnumClassMapper();

		$this->assertTrue($mapper->isSupported(TestBackedStringEnum::class));
		$this->assertTrue($mapper->isSupported(TestBackedIntEnum::class));
		$this->assertFalse($mapper->isSupported(stdClass::class));
		$this->assertFalse($mapper->isSupported(BackedEnum::class));
	}

	public function testEnumHelperDetectsBackingValueType(): void
	{
		$this->assertSame(EnumHelper::StringType, EnumHelper::getBackingValueType(TestBackedStringEnum::class));
		$this->assertSame(EnumHelper::IntType, EnumHelper::getBackingValueType(TestBackedIntEnum::class));
		$this->assertSame(EnumHelper::UnknownType, EnumHelper::getBackingValueType(TestEmptyBackedEnum::class));
		$this->assertSame(EnumHelper::StringType, EnumHelper::getBackingValueType(TestSingleCaseEnum::class));
	}

	public function testEnumHelperCreatesStringEnum(): void
	{
		$this->assertSame(TestBackedStringEnum::Second, EnumHelper::createStringEnum(TestBackedStringEnum::class, 'second'));
		$this->assertNull(EnumHelper::createStringEnum(TestBackedStringEnum::class, 'third'));
	}

	public function testEnumHelperReturnsValueOnlyWhenItMatchesBackingType(): void
	{
		$this->assertSame('first', EnumHelper::getCorrectTypeForBackedEnum('first', TestBackedStringEnum::class));
		$this->assertNull(EnumHelper::getCorrectTypeForBackedEnum(1, TestBackedStringEnum::class));
		$this->assertSame(1, EnumHelper::getCorrectTypeForBackedEnum(1, TestBackedIntEnum::class));
		$this->assertNull(EnumHelper::getCorrectTypeForBackedEnum('1', TestBackedIntEnum::class));
		$this->assertNull(EnumHelper::getCorrectTypeForBackedEnum(1, TestEmptyBackedEnum::class));
		$this->assertSame('only', EnumHelper::getCorrectTypeForBackedEnum('only', TestSingleCaseEnum::class));
	}

	private function createLenientProcessor(): TypeSchemaProcessor
	{
		return TypeSchemaProcessor::createDefault(ConversionStrategyFactory::lenient());
	}

}
