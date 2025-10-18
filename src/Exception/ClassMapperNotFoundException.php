<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Exception;

final class ClassMapperNotFoundException extends \LogicException
{

	/**
	 * @param class-string $className
	 */
	public function __construct(string $className)
	{
		parent::__construct(sprintf('Class mapper for class "%s" not found.', $className));
	}

}
