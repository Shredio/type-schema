<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

final readonly class ObjectMapperCompilerContext
{

	/** @var callable(ObjectMapperToCompile<object>): bool */
	private mixed $hasProviderFor;

	/**
	 * @no-named-arguments
	 * @param callable(ObjectMapperToCompile<object>): bool $hasProviderFor
	 */
	public function __construct(
		private ObjectMapperCompileInfoProvider $compileInfoProvider,
		callable $hasProviderFor,
	)
	{
		$this->hasProviderFor = $hasProviderFor;
	}

	/**
	 * @no-named-arguments
	 * @param ObjectMapperToCompile<object> $mapperToCompile
	 */
	public function hasProviderFor(ObjectMapperToCompile $mapperToCompile): bool
	{
		return ($this->hasProviderFor)($mapperToCompile);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return ObjectMapperToCompile<T>
	 */
	public function createObjectMapperToCompile(string $className): ObjectMapperToCompile
	{
		return $this->compileInfoProvider->provide($className);
	}

}
