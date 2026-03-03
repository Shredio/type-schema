<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Number;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;

final readonly class StrictNumberConverter implements NumberConverter, ConstructableConverter
{

	public function int(mixed $value): ?int
	{
		if (is_int($value)) {
			return $value;
		}

		return null;
	}

	public function float(mixed $value): ?float
	{
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
