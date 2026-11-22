<?php declare(strict_types = 1);

namespace Tests\Unit\Mapper;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Mapper\BackedEnumClassMapper;
use Shredio\TypeSchema\Mapper\CacheableClassMapper;
use Shredio\TypeSchema\Mapper\ClassMapper;
use Shredio\TypeSchema\Mapper\DateTimeClassMapper;
use Shredio\TypeSchema\Mapper\RegistryClassMapperProvider;
use Shredio\TypeSchema\Types\ClassMapperType;
use Shredio\TypeSchema\Types\Type;
use stdClass;
use Tests\Unit\Mapper\Fixtures\TestBackedStringEnum;

final class RegistryClassMapperProviderTest extends TestCase
{

	public function testDefaultMappersSupportEnumsAndDates(): void
	{
		$mappers = RegistryClassMapperProvider::createDefaultClassMappers();

		$this->assertCount(2, $mappers);
		$this->assertInstanceOf(BackedEnumClassMapper::class, $mappers[0]);
		$this->assertInstanceOf(DateTimeClassMapper::class, $mappers[1]);

		$provider = new RegistryClassMapperProvider($mappers);
		$context = $this->createContext($provider);

		$this->assertSame(TestBackedStringEnum::First, $provider->provide(TestBackedStringEnum::class)?->parse('first', $context));
		$this->assertInstanceOf(DateTimeImmutable::class, $provider->provide(DateTimeImmutable::class)?->parse('2024-01-15', $context));
	}

	public function testReturnsNullForUnsupportedClassWithoutInnerProvider(): void
	{
		$provider = new RegistryClassMapperProvider(RegistryClassMapperProvider::createDefaultClassMappers());

		$this->assertNull($provider->provide(stdClass::class));
	}

	public function testFallsBackToInnerProvider(): void
	{
		$inner = new RegistryClassMapperProvider([new StdClassMapper()]);
		$provider = new RegistryClassMapperProvider([new DateTimeClassMapper()], $inner);

		$this->assertInstanceOf(ClassMapperType::class, $provider->provide(stdClass::class));
		$this->assertNull((new RegistryClassMapperProvider([]))->provide(stdClass::class));
	}

	public function testOwnMapperHasPriorityOverInnerProvider(): void
	{
		$ownMapper = new StdClassMapper('own');
		$provider = new RegistryClassMapperProvider([$ownMapper], new RegistryClassMapperProvider([new StdClassMapper('inner')]));

		$this->assertSame('own', $this->createStdClass($provider)->source);
	}

	public function testCacheableMapperIsUsedOnlyForItsClassNames(): void
	{
		$mapper = new CacheableStdClassMapper();
		$provider = new RegistryClassMapperProvider([$mapper]);

		$this->assertSame('cacheable', $this->createStdClass($provider)->source);
		$this->assertSame(0, $mapper->supportChecks->count, 'Cacheable mappers are registered by their class names.');
		$this->assertNull($provider->provide(DateTimeImmutable::class));
	}

	public function testMapperIsLookedUpOnlyOnceForClass(): void
	{
		$mapper = new StdClassMapper();
		$provider = new RegistryClassMapperProvider([$mapper]);

		$provider->provide(stdClass::class);
		$provider->provide(stdClass::class);

		$this->assertSame(1, $mapper->supportChecks->count);
	}

	public function testFirstSupportingMapperWins(): void
	{
		$provider = new RegistryClassMapperProvider([new StdClassMapper('first'), new StdClassMapper('second')]);

		$this->assertSame('first', $this->createStdClass($provider)->source);
	}

	private function createStdClass(RegistryClassMapperProvider $provider): stdClass
	{
		$type = $provider->provide(stdClass::class);
		$this->assertInstanceOf(Type::class, $type);
		$value = $type->parse([], $this->createContext($provider));
		$this->assertInstanceOf(stdClass::class, $value);

		return $value;
	}

	private function createContext(RegistryClassMapperProvider $provider): TypeContext
	{
		return new TypeContext(ConversionStrategyFactory::lenient(), $provider);
	}

}

final class Counter
{

	public int $count = 0;

}

/**
 * @extends ClassMapper<stdClass>
 */
readonly class StdClassMapper extends ClassMapper
{

	public Counter $supportChecks;

	public function __construct(
		private string $source = 'mapper',
	)
	{
		$this->supportChecks = new Counter();
	}

	public function isSupported(string $className): bool
	{
		$this->supportChecks->count++;

		return $className === stdClass::class;
	}

	public function create(string $className, mixed $valueToParse, TypeContext $context): object
	{
		$object = new stdClass();
		$object->source = $this->source;

		return $object;
	}

}

/**
 * @implements CacheableClassMapper<stdClass>
 */
final readonly class CacheableStdClassMapper extends StdClassMapper implements CacheableClassMapper
{

	public function __construct()
	{
		parent::__construct('cacheable');
	}

	public function getSupportedClassNames(): array
	{
		return [stdClass::class];
	}

}
