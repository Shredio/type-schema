<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

interface ClassMapperCompiler
{

	/**
	 * @param ClassMapperToCompile<object> $objectMapperData
	 */
	public function compile(ClassMapperToCompile $objectMapperData, ObjectMapperCompilerContext $context): void;

	/**
	 * @param ClassMapperToCompile<object> $objectMapperData
	 */
	public function needsRecompile(ClassMapperToCompile $objectMapperData): bool;

	/**
	 * Toggles multiprocess safety for the current instance.
	 *
	 * @param bool $enabled Whether to enable or disable multi-process safety.
	 * @return static The current instance with the updated setting.
	 */
	public function withMultiProcessSafety(bool $enabled): static;

}
