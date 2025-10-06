<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

use Shredio\TypeSchema\Mapper\ObjectMapperProvider;
use Shredio\TypeSchema\Types\Type;

final readonly class JustInTimeObjectMapperProvider implements ObjectMapperProvider
{

	public function __construct(
		private ObjectMapperCompileInfoProvider $objectMapperCompileInfoProvider,
		private ?ObjectMapperProvider $innerProvider,
		private ?ObjectMapperCompiler $compiler,
		private bool $raiseWarningsOnMissingClasses = false,
	)
	{
	}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return Type<T>
	 */
	public function provide(string $className): Type
	{
		$mapper = $this->innerProvider?->provide($className);
		if ($mapper !== null) {
			return $mapper;
		}

		$mapperToCompile = $this->objectMapperCompileInfoProvider->provide($className);
		if ($this->compiler !== null && $this->needsToCompile($mapperToCompile)) {
			if ($this->raiseWarningsOnMissingClasses) {
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
				$this->needsToCompile(...),
			);

			$this->compiler->compile($mapperToCompile, $context);
		}

		if ((@include $mapperToCompile->targetFilePath) !== false) { // @ file may not exist
			return new ($mapperToCompile->mapperClassName)();
		}

		throw new \RuntimeException(sprintf('Cannot provide object mapper for class %s', $className));
	}

	/**
	 * @param ObjectMapperToCompile<object> $mapperToCompile
	 */
	private function needsToCompile(ObjectMapperToCompile $mapperToCompile): bool
	{
		if ($this->compiler === null) {
			return true;
		}

		if ($this->compiler->needsRecompile($mapperToCompile)) {
			return true;
		}

		return !file_exists($mapperToCompile->targetFilePath);
	}

}
