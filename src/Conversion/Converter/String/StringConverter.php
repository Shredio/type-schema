<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\String;

interface StringConverter
{

	public function string(mixed $value): ?string;

}
