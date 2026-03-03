<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Bool;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;

final readonly class StringBoolConverter implements BoolConverter, ConstructableConverter
{

	/**
	 * @param list<lowercase-string> $trueValues
	 * @param list<lowercase-string> $falseValues
	 */
	public function __construct(
		private array $trueValues = ['1', 'true'],
		private array $falseValues = ['0', 'false'],
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

		return null;
	}

	public function constructorArguments(): array
	{
		if ($this->trueValues === ['1', 'true'] && $this->falseValues === ['0', 'false']) {
			return [];
		}

		$arguments = [$this->trueValues];
		if ($this->falseValues !== ['0', 'false']) {
			$arguments[] = $this->falseValues;
		}

		return $arguments;
	}

}
