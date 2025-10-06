<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Jit;

interface ObjectMapperCompileInfoProvider
{

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return ObjectMapperToCompile<T>
	 */
	public function provide(string $className): ObjectMapperToCompile;

}
