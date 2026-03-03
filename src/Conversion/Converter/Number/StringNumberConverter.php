<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Number;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;

final readonly class StringNumberConverter implements NumberConverter, ConstructableConverter
{

	public function int(mixed $value): ?int
	{
		if (is_string($value)) {
			return NumberConverterHelper::tryConvertToStrictInt($value);
		}

		if (is_int($value)) {
			return $value;
		}

		return null;
	}

	public function float(mixed $value): ?float
	{
		if (is_string($value) && $value !== '') {
			if (ctype_digit($value)) { // fast check for positive integers
				return (float) $value;
			}

			return NumberConverterHelper::tryConvertLenientFloat($value);
		}

		if (is_float($value)) {
			return $value;
		}

		return null;
	}

	public function constructorArguments(): array
	{
		return [];
	}

}
