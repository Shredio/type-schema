<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Number;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;

final readonly class JsonNumberConverter implements NumberConverter, ConstructableConverter
{

	public const null DisableFloatToInt = null;
	public const float ExactFloatToInt = 0.0;
	public const float AlwaysFloatToInt = PHP_FLOAT_MAX;
	public const float DefaultFloatToIntEpsilon = 1e-7;

	/**
	 * @param int<1, 4>|\RoundingMode $roundingMode
	 */
	public function __construct(
		private ?float $floatToIntEpsilon = self::DisableFloatToInt,
		private int|\RoundingMode $roundingMode = PHP_ROUND_HALF_UP,
	)
	{
	}

	public function int(mixed $value): ?int
	{
		if (is_int($value)) {
			return $value;
		}

		if (is_float($value) && $this->floatToIntEpsilon !== null) {
			$int = (int) round($value, mode: $this->roundingMode);

			if (abs($value - $int) <= $this->floatToIntEpsilon) {
				return $int;
			}

			return null;
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

	public function constructorArguments(): array
	{
		return [$this->floatToIntEpsilon, $this->roundingMode];
	}

}
