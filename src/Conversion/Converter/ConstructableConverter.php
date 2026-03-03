<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter;

use UnitEnum;

interface ConstructableConverter
{

	/**
	 * @return list<scalar|UnitEnum|null|array<array-key, scalar|UnitEnum|null>>
	 */
	public function constructorArguments(): array;

}
