<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Null;

interface NullConverter
{

	public function null(mixed $value): null|false;

}
