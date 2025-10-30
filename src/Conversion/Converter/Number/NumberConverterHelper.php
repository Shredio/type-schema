<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Number;

final readonly class NumberConverterHelper
{

	public static function tryConvertToStrictInt(string $value): ?int
	{
		if (ctype_digit($value)) {
			return (int) $value;
		}
		if (str_starts_with($value, '-') && ctype_digit(substr($value, 1))) {
			return (int) $value;
		}

		return null;
	}

	public static function tryConvertLenientFloat(string $value): ?float
	{
		if (preg_match('/^[+-]?(?:\d+(?:[.]\d*)?(?:[eE][+-]?\d+)?|[.]\d+(?:[eE][+-]?\d+)?)$/D', $value)) {
			return (float) $value;
		}

		return null;
	}

}
