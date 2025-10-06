<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Object;

interface ObjectSupervisor
{

	/**
	 * @param class-string $className
	 */
	public function isStrict(string $className): bool;

}
