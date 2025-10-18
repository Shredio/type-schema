<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use Shredio\TypeSchema\Types\ClassMapperType;
use Shredio\TypeSchema\Types\Type;

final class RegistryClassMapperProvider implements ClassMapperProvider
{

	/** @var array<class-string<object>, ClassMapper<covariant object>> */
	private array $cachedRegistry;

	/** @var list<ClassMapper<covariant object>> */
	private array $registry;

	/**
	 * @param iterable<ClassMapper<covariant object>> $mappers
	 */
	public function __construct(
		iterable $mappers,
		private readonly ?ClassMapperProvider $innerProvider = null,
	)
	{
		$cachedRegistry = [];
		$registry = [];
		foreach ($mappers as $mapper) {
			if ($mapper instanceof CacheableClassMapper) {
				foreach ($mapper->getSupportedClassNames() as $className) {
					$cachedRegistry[$className] = $mapper;
				}
			} else {
				$registry[] = $mapper;
			}
		}

		$this->cachedRegistry = $cachedRegistry;
		$this->registry = $registry;
	}

	/**
	 * @return non-empty-list<ClassMapper<covariant object>>
	 */
	public static function createDefaultClassMappers(): array
	{
		return [
			new BackedEnumClassMapper(),
			new DateTimeClassMapper(),
		];
	}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return Type<T>|null
	 */
	public function provide(string $className): ?Type
	{
		/** @var ClassMapper<T>|null $mapper */
		$mapper = $this->cachedRegistry[$className] ?? null;
		if ($mapper !== null) {
			return new ClassMapperType($className, $mapper);
		}

		/** @var ClassMapper<T> $mapper */
		foreach ($this->registry as $mapper) {
			if ($mapper->isSupported($className)) {
				$this->cachedRegistry[$className] = $mapper;

				return new ClassMapperType($className, $mapper);
			}
		}

		return $this->innerProvider?->provide($className);
	}

}
