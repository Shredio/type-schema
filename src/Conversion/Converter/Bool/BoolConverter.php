<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Bool;

interface BoolConverter
{

	public function bool(mixed $value): ?bool;

}
