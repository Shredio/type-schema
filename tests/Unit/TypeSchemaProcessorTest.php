<?php declare(strict_types = 1);

namespace Tests\Unit;

use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Exception\AssertException;
use Shredio\TypeSchema\Issue\Renderer\EnglishIssueRenderer;
use Shredio\TypeSchema\Issue\Renderer\SymfonyIssueRenderer;
use Shredio\TypeSchema\Mapper\ClassMapper;
use Shredio\TypeSchema\Mapper\Options\DateTimeOptions;
use Shredio\TypeSchema\Mapper\RegistryClassMapperProvider;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use stdClass;
use Symfony\Component\Translation\IdentityTranslator;
use Tests\Unit\Mapper\Fixtures\TestBackedStringEnum;

final class TypeSchemaProcessorTest extends TestCase
{

	public function testCreateDefaultIsStrict(): void
	{
		$this->assertFalse(TypeSchemaProcessor::createDefault()->matches('5', TypeSchema::get()->int()));
	}

	public function testCreateDefaultUsesGivenConversionStrategy(): void
	{
		$processor = TypeSchemaProcessor::createDefault(ConversionStrategyFactory::lenient());

		$this->assertSame(5, $processor->process('5', TypeSchema::get()->int()));
	}

	public function testCreateDefaultRegistersDefaultClassMappers(): void
	{
		$processor = TypeSchemaProcessor::createDefault(ConversionStrategyFactory::lenient());

		$this->assertSame(TestBackedStringEnum::First, $processor->process('first', TypeSchema::get()->mapper(TestBackedStringEnum::class)));
		$this->assertInstanceOf(DateTimeImmutable::class, $processor->process('2024-01-15', TypeSchema::get()->mapper(DateTimeImmutable::class)));
	}

	public function testCreateDefaultUsesGivenClassMapperProvider(): void
	{
		$processor = TypeSchemaProcessor::createDefault(
			ConversionStrategyFactory::lenient(),
			new RegistryClassMapperProvider([$this->createStdClassMapper('processor')]),
		);

		$this->assertSame('processor', $processor->process([], TypeSchema::get()->mapper(stdClass::class))->source);
	}

	public function testCreateDefaultUsesEnglishIssueRenderer(): void
	{
		$processor = TypeSchemaProcessor::createDefault();

		$this->assertInstanceOf(EnglishIssueRenderer::class, $processor->getIssueRenderer());
		$this->assertSame('Please provide a valid int.', (string) $this->catchAssertException($processor, 'x')->getErrors()[0]->message);
	}

	public function testCreateDefaultUsesGivenIssueRenderer(): void
	{
		$renderer = new SymfonyIssueRenderer(new IdentityTranslator());
		$processor = TypeSchemaProcessor::createDefault(issueRenderer: $renderer);

		$this->assertSame($renderer, $processor->getIssueRenderer());
		$this->assertSame('This value should be of type int.', (string) $this->catchAssertException($processor, 'x')->getErrors()[0]->message);
	}

	public function testDefaultOptionsAreUsedWithoutConfig(): void
	{
		$processor = $this->createProcessorWithDateFormat('!d.m.Y');

		$this->assertSame('2024-01-15', $processor->process('15.01.2024', TypeSchema::get()->mapper(DateTimeImmutable::class))->format('Y-m-d'));
	}

	public function testConfigOptionsAreMergedWithDefaultOptions(): void
	{
		$processor = $this->createProcessorWithDateFormat('!d.m.Y');
		$config = new TypeConfig(options: TypeConfig::buildOptions([new stdClass()]));

		$result = $processor->parse('15.01.2024', TypeSchema::get()->mapper(DateTimeImmutable::class), $config);

		$this->assertInstanceOf(Success::class, $result);
	}

	public function testConfigOptionsOverrideDefaultOptions(): void
	{
		$processor = $this->createProcessorWithDateFormat('!d.m.Y');
		$config = new TypeConfig(options: TypeConfig::buildOptions([new DateTimeOptions(formats: ['!Y/m/d'])]));
		$type = TypeSchema::get()->mapper(DateTimeImmutable::class);

		$this->assertInstanceOf(Success::class, $processor->parse('2024/01/15', $type, $config));
		$this->assertInstanceOf(Failure::class, $processor->parse('15.01.2024', $type, $config));
	}

	public function testConfigConversionStrategyOverridesProcessorStrategy(): void
	{
		$processor = TypeSchemaProcessor::createDefault(ConversionStrategyFactory::strict());

		$this->assertSame(5, $processor->process('5', TypeSchema::get()->int(), new TypeConfig(ConversionStrategyFactory::lenient())));
	}

	public function testConfigWithoutConversionStrategyKeepsProcessorStrategy(): void
	{
		$processor = TypeSchemaProcessor::createDefault(ConversionStrategyFactory::lenient());

		$this->assertSame(5, $processor->process('5', TypeSchema::get()->int(), new TypeConfig()));
	}

	public function testConfigClassMapperProviderOverridesProcessorProvider(): void
	{
		$processor = TypeSchemaProcessor::createDefault(
			ConversionStrategyFactory::lenient(),
			new RegistryClassMapperProvider([$this->createStdClassMapper('processor')]),
		);
		$config = new TypeConfig(classMapperProvider: new RegistryClassMapperProvider([$this->createStdClassMapper('config')]));
		$type = TypeSchema::get()->mapper(stdClass::class);

		$this->assertSame('config', $processor->process([], $type, $config)->source);
		$this->assertSame('processor', $processor->process([], $type, new TypeConfig())->source);
	}

	public function testMatchesRequiresValueWithoutNotices(): void
	{
		$t = TypeSchema::get();
		$processor = TypeSchemaProcessor::createDefault();
		$type = $t->arrayShape(['id' => $t->int()]);

		$this->assertTrue($processor->matches(['id' => 1], $type));
		$this->assertFalse($processor->matches(['id' => 1, 'extra' => 2], $type));
		$this->assertFalse($processor->matches(['id' => 'x'], $type));
	}

	public function testProcessCollectsAllErrorsAndProcessFastStopsAtFirst(): void
	{
		$t = TypeSchema::get();
		$processor = TypeSchemaProcessor::createDefault();
		$type = $t->arrayShape(['a' => $t->int(), 'b' => $t->int()]);

		$this->assertCount(2, $this->catchAssertException($processor, ['a' => 'x', 'b' => 'y'], $type)->getErrors());
		$this->assertCount(1, $this->catchAssertException($processor, ['a' => 'x', 'b' => 'y'], $type, fast: true)->getErrors());
	}

	public function testProcessTreatsNoticesAsErrors(): void
	{
		$t = TypeSchema::get();
		$processor = TypeSchemaProcessor::createDefault();
		$type = $t->arrayShape(['a' => $t->int()]);

		foreach ([false, true] as $fast) {
			$exception = $this->catchAssertException($processor, ['a' => 1, 'extra' => 2], $type, $fast);
			$this->assertSame(['extra'], $exception->getErrors()[0]->toArrayPath());
		}
	}

	public function testParseWrapsCleanAndNoticedValuesIntoSuccess(): void
	{
		$t = TypeSchema::get();
		$processor = TypeSchemaProcessor::createDefault();
		$type = $t->arrayShape(['a' => $t->int()]);

		$clean = $processor->parse(['a' => 1], $type);
		$this->assertInstanceOf(Success::class, $clean);
		$this->assertNull($clean->notices);

		$withNotices = $processor->parse(['a' => 1, 'extra' => 2], $type);
		$this->assertInstanceOf(Success::class, $withNotices);
		$this->assertSame(['a' => 1], $withNotices->value);
		$this->assertTrue($withNotices->hasNotices());
	}

	public function testAssertExceptionCarriesFailure(): void
	{
		$failure = TypeSchemaProcessor::createDefault()->parse('x', TypeSchema::get()->int());
		$this->assertInstanceOf(Failure::class, $failure);
		$previous = new LogicException('previous');

		$exception = new AssertException($failure, previous: $previous);

		$this->assertSame('Assertion failed.', $exception->getMessage());
		$this->assertSame($previous, $exception->getPrevious());
		$this->assertSame($failure, $exception->getFailure());
		$this->assertSame('Please provide a valid int.', (string) $exception->getErrors()[0]->message);
	}

	private function createProcessorWithDateFormat(string $format): TypeSchemaProcessor
	{
		return TypeSchemaProcessor::createDefault(
			ConversionStrategyFactory::lenient(),
			defaultOptions: TypeConfig::buildOptions([new DateTimeOptions(formats: [$format])]),
		);
	}

	/**
	 * @param Type<mixed>|null $type
	 */
	private function catchAssertException(TypeSchemaProcessor $processor, mixed $value, ?Type $type = null, bool $fast = false): AssertException
	{
		$type ??= TypeSchema::get()->int();
		try {
			$fast ? $processor->processFast($value, $type) : $processor->process($value, $type);
		} catch (AssertException $exception) {
			return $exception;
		}

		$this->fail('AssertException was not thrown.');
	}

	/**
	 * @return ClassMapper<stdClass>
	 */
	private function createStdClassMapper(string $source): ClassMapper
	{
		return new readonly class ($source) extends ClassMapper {

			public function __construct(
				private string $source,
			)
			{
			}

			public function isSupported(string $className): bool
			{
				return $className === stdClass::class;
			}

			public function create(string $className, mixed $valueToParse, TypeContext $context): object
			{
				$object = new stdClass();
				$object->source = $this->source;

				return $object;
			}

		};
	}

}
