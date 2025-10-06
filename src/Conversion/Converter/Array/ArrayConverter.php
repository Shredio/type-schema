<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Array;

interface ArrayConverter
{

	/**
	 * @return mixed[]|null
	 */
	public function array(mixed $value, bool $preserveKeys): ?array;

}
