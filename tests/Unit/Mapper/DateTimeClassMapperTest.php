<?php declare(strict_types = 1);

namespace Tests\Unit\Mapper;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Issue\InvalidDate;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\NumberOutOfRange;
use Shredio\TypeSchema\Mapper\DateTimeClassMapper;
use Shredio\TypeSchema\Mapper\Options\DateTimeOptions;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use stdClass;
use Tests\Unit\Mapper\Fixtures\TestDateTimeImmutable;
use Tests\Unit\Mapper\Fixtures\TestDateTimeInterfaceExtension;

final class DateTimeClassMapperTest extends TestCase
{

	private string $originalTimeZone;

	protected function setUp(): void
	{
		$this->originalTimeZone = date_default_timezone_get();
		date_default_timezone_set('UTC');
	}

	protected function tearDown(): void
	{
		date_default_timezone_set($this->originalTimeZone);
	}

	/**
	 * @return iterable<string, array{string, string}>
	 */
	public static function provideDefaultFormats(): iterable
	{
		yield 'atom' => ['2024-01-15T10:20:30+02:00', '2024-01-15T10:20:30.000000+02:00'];
		yield 'rfc3339 extended' => ['2024-01-15T10:20:30.123+02:00', '2024-01-15T10:20:30.123000+02:00'];
		yield 'date and time' => ['2024-01-15 10:20:30', '2024-01-15T10:20:30.000000+00:00'];
		yield 'date only is midnight' => ['2024-01-15', '2024-01-15T00:00:00.000000+00:00'];
	}

	#[DataProvider('provideDefaultFormats')]
	public function testParsesDefaultFormats(string $value, string $expected): void
	{
		$dateTime = $this->parseValid($value, DateTimeImmutable::class);

		$this->assertSame($expected, $dateTime->format('Y-m-d\TH:i:s.uP'));
	}

	/**
	 * @return iterable<string, array{class-string<DateTimeInterface>, class-string<DateTimeInterface>}>
	 */
	public static function provideTargetClasses(): iterable
	{
		yield 'immutable' => [DateTimeImmutable::class, DateTimeImmutable::class];
		yield 'mutable' => [DateTime::class, DateTime::class];
		yield 'interface maps to immutable' => [DateTimeInterface::class, DateTimeImmutable::class];
		yield 'subclass' => [TestDateTimeImmutable::class, TestDateTimeImmutable::class];
	}

	/**
	 * @param class-string<DateTimeInterface> $className
	 * @param class-string<DateTimeInterface> $expectedClassName
	 */
	#[DataProvider('provideTargetClasses')]
	public function testCreatesInstanceOfTargetClass(string $className, string $expectedClassName): void
	{
		$dateTime = $this->parseValid('2024-01-15 10:20:30', $className);

		$this->assertSame($expectedClassName, $dateTime::class);
		$this->assertSame('2024-01-15 10:20:30', $dateTime->format('Y-m-d H:i:s'));
	}

	/**
	 * @return iterable<string, array{string}>
	 */
	public static function provideInvalidDates(): iterable
	{
		yield 'garbage' => ['not a date'];
		yield 'empty string' => [''];
		yield 'overflowing day' => ['2024-02-30'];
		yield 'overflowing month' => ['2024-13-01'];
		yield 'overflowing time' => ['2024-01-15 25:61:00'];
		yield 'trailing data' => ['2024-01-15 garbage'];
		yield 'relative format without constructor' => ['tomorrow'];
	}

	#[DataProvider('provideInvalidDates')]
	public function testRejectsInvalidDates(string $value): void
	{
		$result = $this->parse($value, DateTimeImmutable::class);

		$this->assertInstanceOf(Failure::class, $result);
		$issue = $result->getReports()[0]->issue;
		$this->assertInstanceOf(InvalidDate::class, $issue);
		$this->assertSame($value, $issue->value);
		$this->assertSame('Please provide a valid date.', (string) $result->getReports()[0]->message);
	}

	public function testCustomFormats(): void
	{
		$options = new DateTimeOptions(formats: ['!d.m.Y']);

		$this->assertSame('2024-01-15T00:00:00', $this->parseValid('15.01.2024', DateTimeImmutable::class, $options)->format('Y-m-d\TH:i:s'));
		$this->assertInstanceOf(Failure::class, $this->parse('2024-01-15', DateTimeImmutable::class, $options));
	}

	public function testTimeZoneOption(): void
	{
		$options = new DateTimeOptions(timeZone: new DateTimeZone('Europe/Prague'));

		$dateTime = $this->parseValid('2024-01-15 10:20:30', DateTimeImmutable::class, $options);

		$this->assertSame('Europe/Prague', $dateTime->getTimezone()->getName());
		$this->assertSame('2024-01-15T10:20:30+01:00', $dateTime->format(DateTimeInterface::ATOM));
	}

	public function testConstructorOptionAcceptsRelativeFormats(): void
	{
		$options = new DateTimeOptions(constructor: true);

		$dateTime = $this->parseValid('tomorrow', DateTimeImmutable::class, $options);

		$this->assertSame((new DateTimeImmutable('tomorrow'))->format('Y-m-d'), $dateTime->format('Y-m-d'));
		$this->assertSame('00:00:00', $dateTime->format('H:i:s'));
	}

	public function testConstructorOptionRejectsInvalidDates(): void
	{
		$options = new DateTimeOptions(formats: ['!d.m.Y'], constructor: true);

		$this->assertInstanceOf(Failure::class, $this->parse('not a date', DateTimeImmutable::class, $options));
		$this->assertInstanceOf(Failure::class, $this->parse('2024-02-30', DateTimeImmutable::class, $options));
	}

	public function testIntegerIsRejectedUnlessTimestampsAreAllowed(): void
	{
		$result = $this->parse(1705314030, DateTimeImmutable::class);

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertInstanceOf(InvalidType::class, $result->getReports()[0]->issue);
		$this->assertSame('Invalid type int with value 1705314030, expected string.', $result->getReports()[0]->messageForDeveloper);
	}

	public function testIntegerTimestamp(): void
	{
		$options = new DateTimeOptions(allowIntAsTimestamp: true);

		$this->assertSame(1705314030, $this->parseValid(1705314030, DateTimeImmutable::class, $options)->getTimestamp());
		$this->assertSame(0, $this->parseValid(0, DateTime::class, $options)->getTimestamp());
		$this->assertSame(DateTimeImmutable::class, $this->parseValid(0, DateTimeInterface::class, $options)::class);
	}

	public function testNegativeTimestampIsOutOfRange(): void
	{
		$result = $this->parse(-1, DateTimeImmutable::class, new DateTimeOptions(allowIntAsTimestamp: true));

		$this->assertInstanceOf(Failure::class, $result);
		$issue = $result->getReports()[0]->issue;
		$this->assertInstanceOf(NumberOutOfRange::class, $issue);
		$this->assertSame(RangeInclusiveDecision::ShouldBeGreaterOrEqual, $issue->decision);
		$this->assertSame('Must be at least 0.', (string) $result->getReports()[0]->message);
		$this->assertSame('Value -1 is not in the expected range [0, max].', $result->getReports()[0]->messageForDeveloper);
	}

	public function testOtherTypesAreRejectedWithExpectedTypes(): void
	{
		$withoutTimestamps = $this->parse(1.5, DateTimeImmutable::class);
		$withTimestamps = $this->parse(1.5, DateTimeImmutable::class, new DateTimeOptions(allowIntAsTimestamp: true));

		$this->assertInstanceOf(Failure::class, $withoutTimestamps);
		$this->assertSame('Invalid type float with value 1.5, expected string.', $withoutTimestamps->getReports()[0]->messageForDeveloper);
		$this->assertInstanceOf(Failure::class, $withTimestamps);
		$this->assertSame('Invalid type float with value 1.5, expected (string | int).', $withTimestamps->getReports()[0]->messageForDeveloper);
	}

	public function testStrictObjectStrategyAcceptsOnlyInstances(): void
	{
		$processor = TypeSchemaProcessor::createDefault(ConversionStrategyFactory::strict());
		$dateTime = new DateTimeImmutable('2024-01-15');

		$this->assertSame($dateTime, $processor->process($dateTime, TypeSchema::get()->mapper(DateTimeInterface::class)));
		$this->assertInstanceOf(Failure::class, $processor->parse('2024-01-15', TypeSchema::get()->mapper(DateTimeInterface::class)));
	}

	public function testSupportedClasses(): void
	{
		$mapper = new DateTimeClassMapper();

		$this->assertTrue($mapper->isSupported(DateTimeInterface::class));
		$this->assertTrue($mapper->isSupported(DateTime::class));
		$this->assertTrue($mapper->isSupported(DateTimeImmutable::class));
		$this->assertTrue($mapper->isSupported(TestDateTimeImmutable::class));
		$this->assertFalse($mapper->isSupported(TestDateTimeInterfaceExtension::class));
		$this->assertFalse($mapper->isSupported(stdClass::class));
	}

	/**
	 * @template T of DateTimeInterface
	 * @param class-string<T> $className
	 * @return T
	 */
	private function parseValid(mixed $value, string $className, ?DateTimeOptions $options = null): DateTimeInterface
	{
		$result = $this->parse($value, $className, $options);
		$this->assertInstanceOf(Success::class, $result);
		$this->assertInstanceOf($className, $result->value);

		return $result->value;
	}

	/**
	 * @param class-string<DateTimeInterface> $className
	 * @return Success<DateTimeInterface>|Failure
	 */
	private function parse(mixed $value, string $className, ?DateTimeOptions $options = null): Success|Failure
	{
		$processor = TypeSchemaProcessor::createDefault(ConversionStrategyFactory::lenient());
		$config = new TypeConfig(options: TypeConfig::buildOptions($options === null ? [] : [$options]));

		return $processor->parse($value, TypeSchema::get()->mapper($className), $config);
	}

}
