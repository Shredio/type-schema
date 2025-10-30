<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Number;

final readonly class StringNumberConverter implements NumberConverter
{

	public function int(mixed $value): ?int
	{
		if (is_string($value)) {
			if (ctype_digit($value)) {
				return (int) $value;
			}

			return null;
		}

		if (is_int($value)) {
			return $value;
		}

		return null;
	}

	public function float(mixed $value): ?float
	{
		if (is_string($value) && $value !== '') {
			if (ctype_digit($value)) {
				return (float) $value;
			}

			if (preg_match("/^[+-]?(?:\d+(?:[.]\d*)?(?:[eE][+-]?\d+)?|[.]\d+(?:[eE][+-]?\d+)?)$/", $value)) {
				return (float) $value;
			}

			return null;
		}

		if (is_float($value)) {
			return $value;
		}

		return null;
	}

}
