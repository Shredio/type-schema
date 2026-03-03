<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Array;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;
use stdClass;

final readonly class LenientArrayConverter implements ArrayConverter, ConstructableConverter
{

	public function array(mixed $value, bool $preserveKeys): ?array
	{
		if (is_array($value)) {
			return $value;
		}

		if (is_iterable($value)) {
			return iterator_to_array($value, $preserveKeys);
		}

		if ($value instanceof stdClass) {
			return (array) $value;
		}

		return null;
	}

	public function constructorArguments(): array
	{
		return [];
	}

}
