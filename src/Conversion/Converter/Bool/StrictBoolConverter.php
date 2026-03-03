<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Bool;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;

final readonly class StrictBoolConverter implements BoolConverter, ConstructableConverter
{

	public function bool(mixed $value): ?bool
	{
		if (is_bool($value)) {
			return $value;
		}

		return null;
	}

	public function constructorArguments(): array
	{
		return [];
	}

}
