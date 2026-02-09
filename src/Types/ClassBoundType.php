<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

/**
 * @template T of object
 */
interface ClassBoundType
{

	/**
	 * @return class-string<T>
	 */
	public function getClassName(): string;

}
