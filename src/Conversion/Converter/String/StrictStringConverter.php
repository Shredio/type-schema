<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\String;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;

final readonly class StrictStringConverter implements StringConverter, ConstructableConverter
{

	public function string(mixed $value): ?string
	{
		if (is_string($value)) {
			return $value;
		}

		return null;
	}

	public function constructorArguments(): array
	{
		return [];
	}

}
