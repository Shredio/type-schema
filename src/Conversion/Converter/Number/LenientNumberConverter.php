<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Number;

final readonly class LenientNumberConverter implements NumberConverter
{

	public function __construct(
		private bool $checkFloatPrecisionOnCastToInt = true,
	)
	{
	}

	public function int(mixed $value): ?int
	{
		if (is_int($value)) {
			return $value;
		}

		if (is_float($value)) {
			if (!$this->checkFloatPrecisionOnCastToInt) {
				return (int) $value;
			}

			$int = (int) $value;
			if ((float) $int === $value) {
				return $int;
			}

			return null;
		}

		if (is_string($value)) {
			$filtered = filter_var($value, FILTER_VALIDATE_INT);
			if ($filtered !== false) {
				return $filtered;
			}
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
			if (ctype_digit($value)) {
				return (float) $value;
			}

			if (preg_match("/^[+-]?(?:\d+(?:[.]\d*)?(?:[eE][+-]?\d+)?|[.]\d+(?:[eE][+-]?\d+)?)$/", $value)) {
				return (float) $value;
			}
		}

		return null;
	}

}
