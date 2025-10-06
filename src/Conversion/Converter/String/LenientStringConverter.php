<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\String;

final readonly class LenientStringConverter implements StringConverter
{

	public function string(mixed $value): ?string
	{
		if (is_string($value)) {
			return $value;
		}

		if (is_int($value)) {
			return (string) $value;
		}

		if (is_float($value)) {
			return (string) $value;
		}

		return null;
	}

}
