<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

/**
 * @template T of object
 */
interface CacheableClassMapper
{

	/**
	 * @return non-empty-list<class-string<T>>
	 */
	public function getSupportedClassNames(): array;

}
