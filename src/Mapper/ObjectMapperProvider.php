<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use Shredio\TypeSchema\Types\Type;

interface ObjectMapperProvider
{

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return Type<T>|null
	 */
	public function provide(string $className): ?Type;

}
