<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

interface ClassMapperCompileTargetProvider
{

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return ClassMapperToCompile<T>
	 */
	public function provide(string $className): ClassMapperToCompile;

}
