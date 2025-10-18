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

}
