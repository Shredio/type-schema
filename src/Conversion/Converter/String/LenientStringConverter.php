<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\String;

final readonly class LenientStringConverter implements StringConverter
{

	/** @var callable(float): ?string */
	private mixed $floatToStringConverter;

	/**
	 * @param (callable(float): ?string)|null $floatToStringConverter
	 */
	public function __construct(
		?callable $floatToStringConverter = null,
	)
	{
		$this->floatToStringConverter = $floatToStringConverter ?? $this->defaultFloatToStringConverter(...);
	}

	public function string(mixed $value): ?string
	{
		if (is_string($value)) {
			return $value;
		}

		if (is_int($value)) {
			return (string) $value;
		}

		if (is_float($value)) {
			return ($this->floatToStringConverter)($value);
		}

		return null;
	}

	private function defaultFloatToStringConverter(float $value): string
	{
		if (!is_finite($value)) {
			if (is_nan($value)) {
				return 'NAN';
			}

			if ($value === INF) {
				return 'INF';
			}

			return '-INF';
		}

		return (string) $value;
	}

}
