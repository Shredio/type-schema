<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Exception;

final class ObjectMapperNotFoundException extends \LogicException
{

	/**
	 * @param class-string $className
	 */
	public function __construct(string $className)
	{
		parent::__construct(sprintf('Object mapper for class "%s" not found.', $className));
	}

}
