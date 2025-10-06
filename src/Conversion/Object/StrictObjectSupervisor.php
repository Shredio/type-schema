<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Object;

final readonly class StrictObjectSupervisor implements ObjectSupervisor
{

	public function isStrict(string $className): bool
	{
		return true;
	}

}
