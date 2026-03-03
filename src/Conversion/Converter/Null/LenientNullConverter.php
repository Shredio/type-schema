<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Null;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;

final readonly class LenientNullConverter implements NullConverter, ConstructableConverter
{

	public function null(mixed $value): null|false
	{
		if ($value === null || $value === '') {
			return null;
		}

		return false;
	}

	public function constructorArguments(): array
	{
		return [];
	}

}
