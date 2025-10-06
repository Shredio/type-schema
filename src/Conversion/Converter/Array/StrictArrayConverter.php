<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Array;

use Generator;

final readonly class StrictArrayConverter implements ArrayConverter
{

	public function array(mixed $value, bool $preserveKeys): ?array
	{
		if (is_array($value)) {
			return $value;
		}

		if ($value instanceof Generator) {
			return iterator_to_array($value, $preserveKeys);
		}

		return null;
	}

}
