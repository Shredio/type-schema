<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\String;

final readonly class StrictStringConverter implements StringConverter
{

	public function string(mixed $value): ?string
	{
		if (is_string($value)) {
			return $value;
		}

		return null;
	}

}
