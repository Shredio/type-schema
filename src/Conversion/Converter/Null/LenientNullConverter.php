<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Null;

final readonly class LenientNullConverter implements NullConverter
{

	public function null(mixed $value): null|false
	{
		if ($value === null || $value === '') {
			return null;
		}

		return false;
	}

}
