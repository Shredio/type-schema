<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Bool;

final readonly class StrictBoolConverter implements BoolConverter
{

	public function bool(mixed $value): ?bool
	{
		if (is_bool($value)) {
			return $value;
		}

		return null;
	}

}
