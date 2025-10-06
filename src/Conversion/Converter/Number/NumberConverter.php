<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Number;

interface NumberConverter
{

	public function int(mixed $value): ?int;

	public function float(mixed $value): ?float;

}
