<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Exception;

final class ClassMapperNotFoundException extends \LogicException
{

	/**
	 * @param class-string $className
	 */
	public static function notFound(string $className): self
	{
		return new self(sprintf('Class mapper for class "%s" not found.', $className));
	}

}
