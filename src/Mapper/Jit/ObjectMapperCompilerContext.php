<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

final readonly class ObjectMapperCompilerContext
{

	/** @var callable(ClassMapperToCompile<object>): bool */
	private mixed $needsToCompile;

	/**
	 * @no-named-arguments
	 * @param callable(ClassMapperToCompile<object>): bool $needsToCompile
	 */
	public function __construct(
		private ClassMapperCompileTargetProvider $compileInfoProvider,
		callable $needsToCompile,
	)
	{
		$this->needsToCompile = $needsToCompile;
	}

	/**
	 * @no-named-arguments
	 * @param ClassMapperToCompile<object> $mapperToCompile
	 */
	public function hasProviderFor(ClassMapperToCompile $mapperToCompile): bool
	{
		return !($this->needsToCompile)($mapperToCompile);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return ClassMapperToCompile<T>
	 */
	public function createClassMapperToCompile(string $className): ClassMapperToCompile
	{
		return $this->compileInfoProvider->provide($className);
	}

}
