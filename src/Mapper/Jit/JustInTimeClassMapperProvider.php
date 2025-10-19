<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

use Shredio\TypeSchema\Mapper\ClassMapperProvider;
use Shredio\TypeSchema\Mapper\WarmableClassMapperProvider;
use Shredio\TypeSchema\Types\Type;

final readonly class JustInTimeClassMapperProvider implements WarmableClassMapperProvider
{

	public function __construct(
		private ClassMapperCompileTargetProvider $objectMapperCompileInfoProvider,
		private ClassMapperCompiler $compiler,
		private ?ClassMapperProvider $innerProvider = null,
		private bool $raiseWarningsOnMissingClasses = false,
	)
	{
	}

	public function warmup(string $className, bool $forceRecompile = true): void
	{
		$this->create($this->compiler->withMultiProcessSafety(false), $className, false, $forceRecompile);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return Type<T>
	 */
	public function provide(string $className): Type
	{
		return $this->create($this->compiler, $className, $this->raiseWarningsOnMissingClasses);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return Type<T>
	 */
	private function create(
		ClassMapperCompiler $compiler,
		string $className,
		bool $raiseWarningsOnMissingClasses,
		bool $forceRecompile = false,
	): Type
	{
		$mapper = $this->innerProvider?->provide($className);
		if ($mapper !== null) {
			return $mapper;
		}

		$mapperToCompile = $this->objectMapperCompileInfoProvider->provide($className);
		if (class_exists($mapperToCompile->mapperClassName, false)) { // Prevents "Cannot redeclare class" errors
			return new ($mapperToCompile->mapperClassName)();
		}

		$needsToCompile = $this->needsToCompileFunction($forceRecompile);
		if ($needsToCompile($mapperToCompile)) {
			if ($raiseWarningsOnMissingClasses) {
				trigger_error(
					sprintf(
						'Class %s is being compiled just-in-time. Consider pre-compiling it to improve performance.',
						$className,
					),
					E_USER_WARNING,
				);
			}

			$context = new ObjectMapperCompilerContext(
				$this->objectMapperCompileInfoProvider,
				$needsToCompile,
			);

			$compiler->compile($mapperToCompile, $context);
		}

		if ((@include $mapperToCompile->targetFilePath) !== false) { // @ file may not exist
			return new ($mapperToCompile->mapperClassName)();
		}

		throw new \RuntimeException(sprintf('Cannot provide object mapper for class %s', $className));
	}

	/**
	 * @return callable(ClassMapperToCompile<object> $mapperToCompile): bool
	 */
	private function needsToCompileFunction(bool $forceRecompile): callable
	{
		return function (ClassMapperToCompile $mapperToCompile) use ($forceRecompile): bool {
			if ($this->innerProvider !== null && $this->innerProvider->provide($mapperToCompile->className) !== null) {
				return false;
			}

			if ($forceRecompile) {
				return true;
			}

			if ($this->compiler->needsRecompile($mapperToCompile)) {
				return true;
			}

			return !file_exists($mapperToCompile->targetFilePath);
		};
	}

}
