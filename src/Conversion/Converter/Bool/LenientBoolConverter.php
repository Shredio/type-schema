<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Bool;

final readonly class LenientBoolConverter implements BoolConverter
{

	/**
	 * @param list<lowercase-string|int> $trueValues
	 * @param list<lowercase-string|int> $falseValues
	 */
	public function __construct(
		private array $trueValues = ['1', 1, 'true'],
		private array $falseValues = ['0', 0, 'false'],
	)
	{
	}

	public function bool(mixed $value): ?bool
	{
		if (is_bool($value)) {
			return $value;
		}

		if (is_string($value)) {
			$lowerValue = strtolower($value);
			if (in_array($lowerValue, $this->trueValues, true)) {
				return true;
			}

			if (in_array($lowerValue, $this->falseValues, true)) {
				return false;
			}
		}

		if (is_int($value)) {
			if (in_array($value, $this->trueValues, true)) {
				return true;
			}

			if (in_array($value, $this->falseValues, true)) {
				return false;
			}
		}

		return null;
	}

}
