<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Number;

final readonly class JsonNumberConverter implements NumberConverter
{

	public function __construct(
		private bool $convertFloatToInt = false,
		private float $convertFloatToIntEpsilon = 1e-7,
	)
	{
	}

	public function int(mixed $value): ?int
	{
		if (is_int($value)) {
			return $value;
		}

		if (
			$this->convertFloatToInt &&
			is_float($value) &&
			abs($value - round($value)) < $this->convertFloatToIntEpsilon
		) {
			return (int) $value;
		}

		return null;
	}

	public function float(mixed $value): ?float
	{
		if (is_float($value) || is_int($value)) {
			return (float) $value;
		}

		return null;
	}

}
