<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Number;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;

final readonly class LenientNumberConverter implements NumberConverter, ConstructableConverter
{

	public const null DisableFloatToInt = null;
	public const float ExactFloatToInt = 0.0;
	public const true AlwaysFloatToInt = true;
	public const float DefaultFloatToIntEpsilon = 1e-7;

	/**
	 * @param int<1, 4>|\RoundingMode $roundingMode
	 */
	public function __construct(
		private null|true|float $floatToIntEpsilon = self::ExactFloatToInt,
		private int|\RoundingMode $roundingMode = PHP_ROUND_HALF_UP,
	)
	{
	}

	public function int(mixed $value): ?int
	{
		if (is_int($value)) {
			return $value;
		}

		if (is_float($value) && is_finite($value) && $this->floatToIntEpsilon !== null) {
			$int = (int) round($value, mode: $this->roundingMode);

			if ($this->floatToIntEpsilon === true || abs($value - $int) <= $this->floatToIntEpsilon) {
				return $int;
			}

			return null;
		}

		if (is_string($value)) {
			return NumberConverterHelper::tryConvertToStrictInt($value);
		}

		return null;
	}

	public function float(mixed $value): ?float
	{
		if (is_float($value)) {
			return $value;
		}

		if (is_int($value)) {
			return (float) $value;
		}

		if (is_string($value) && $value !== '') {
			if (ctype_digit($value)) { // fast check for positive integers
				return (float) $value;
			}

			return NumberConverterHelper::tryConvertLenientFloat($value);
		}

		return null;
	}

	public function constructorArguments(): array
	{
		if ($this->floatToIntEpsilon === self::ExactFloatToInt && $this->roundingMode === PHP_ROUND_HALF_UP) {
			return [];
		}

		$arguments = [$this->floatToIntEpsilon];
		if ($this->roundingMode !== PHP_ROUND_HALF_UP) {
			$arguments[] = $this->roundingMode;
		}

		return $arguments;
	}

}
