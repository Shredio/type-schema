<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

interface ObjectMapperCompiler
{

	/**
	 * @param ObjectMapperToCompile<object> $objectMapperData
	 */
	public function compile(ObjectMapperToCompile $objectMapperData, ObjectMapperCompilerContext $context): void;

	/**
	 * @param ObjectMapperToCompile<object> $objectMapperData
	 */
	public function needsRecompile(ObjectMapperToCompile $objectMapperData): bool;

}
