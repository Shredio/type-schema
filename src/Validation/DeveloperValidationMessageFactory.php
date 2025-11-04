<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Validation;

use DateTimeInterface;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\NumberRange;
use Shredio\TypeSchema\Helper\RangeExclusiveDecision;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;

final readonly class DeveloperValidationMessageFactory
{

	private const int JsonEncodeOptions = JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;
	private const int MaxStringLength = 40;

	public static function invalidType(TypeDefinition $definition, mixed $value): string
	{
		$str = sprintf('Invalid type %s', get_debug_type($value));
		if (is_scalar($value)) {
			$str .= sprintf(' with value %s', self::describeValue($value));
		}
		$str .= sprintf(', expected %s.', $definition->getStringType());

		return $str;
	}

	/**
	 * @param list<string|int> $path
	 */
	public static function missingField(TypeDefinition $definition, array $path = []): string
	{
		$lastKey = array_key_last($path);
		if ($lastKey === null) {
			return 'Key is missing.';
		} else {
			return sprintf('Key \'%s\' is missing.', $path[$lastKey]);
		}
	}

	/**
	 * @param list<string|int> $path
	 */
	public static function extraField(TypeDefinition $definition, array $path = []): string
	{
		$lastKey = array_key_last($path);
		if ($lastKey === null) {
			return 'Extra key found.';
		} else {
			return sprintf('Extra key \'%s\' found.', $path[$lastKey]);
		}
	}

	public static function notEmpty(TypeDefinition $definition, mixed $value): string
	{
		return sprintf('Value should not be empty, %s given.', self::describeValue($value));
	}

	/**
	 * @param list<string|int> $allowedValues
	 */
	public static function valueNotInAllowedValues(TypeDefinition $definition, int|string $value, array $allowedValues): string
	{
		$stringAllowedValues = self::selectOnlyStrings($allowedValues);
		return sprintf(
			'Value %s is not a valid choice%s%s',
			self::describeValue($value),
			self::didYouMean($stringAllowedValues, $value),
			self::possibleValues($stringAllowedValues, 150, ' '),
		);
	}

	public static function numberRangeDecision(
		TypeDefinition $definition,
		mixed $value,
		NumberRange $range,
		RangeExclusiveDecision|RangeInclusiveDecision $decision,
	): string
	{
		return sprintf('Value %s is not in the expected range %s.', self::describeValue($value), $range->toString());
	}

	public static function equalTo(TypeDefinition $definition, mixed $value, string $expected): string
	{
		return sprintf('Value %s is not equal to %s.', self::describeValue($value), $expected);
	}

	public static function itemCountRange(TypeDefinition $definition, int $count, NumberInclusiveRange $range, RangeInclusiveDecision $decision): string
	{
		return sprintf('Item count %d is not in the expected range %s.', $count, $range->toString());
	}

	public static function invalidDate(mixed $value): string
	{
		return sprintf('Invalid date format, %s given.', self::describeValue($value));
	}

	public static function describeValue(mixed $value): string
	{
		if ($value === null || is_bool($value) || is_int($value)) {
			return json_encode($value, self::JsonEncodeOptions);
		}

		if (is_float($value)) {
			return is_finite($value)
				? json_encode($value, self::JsonEncodeOptions)
				: (string) $value;
		}

		if (is_string($value)) {
			$printable = false;
			$truncated = false;

			if (extension_loaded('mbstring')) {
				if (preg_match('#^[^\p{C}]*+$#u', $value) === 1) {
					$printable = true;
					$truncated = mb_strlen($value, 'UTF-8') > self::MaxStringLength;
					$value = $truncated ? mb_substr($value, 0, self::MaxStringLength, 'UTF-8') : $value;
				}
			} else {
				if (preg_match('#^[\x20-\x7F]*+$#', $value) === 1) {
					$printable = true;
					$truncated = strlen($value) > self::MaxStringLength;
					$value = $truncated ? substr($value, 0, self::MaxStringLength) : $value;
				}
			}

			if ($printable) {
				return json_encode($value, self::JsonEncodeOptions) . ($truncated ? ' (truncated)' : '');
			}
		}

		if ($value instanceof DateTimeInterface) {
			if ($value->format('H:i:s') === '00:00:00') {
				return $value->format('Y-m-d (e)');
			}

			return $value->format(DateTimeInterface::RFC3339);
		}

		return get_debug_type($value);
	}

	/**
	 * @param list<string> $possibleValues
	 * @param mixed $value
	 */
	public static function didYouMean(array $possibleValues, mixed $value): string
	{
		if (!is_string($value)) {
			return '.';
		}

		$suggestion = self::getSuggestion($possibleValues, $value);
		if ($suggestion === null) {
			return '.';
		}

		return sprintf(', did you mean %s?', self::describeValue($suggestion));
	}

	/**
	 * @param list<string> $possibleValues
	 */
	public static function possibleValues(array $possibleValues, int $maxLength, string $before = ''): string
	{
		$lastKey = array_key_last($possibleValues);
		if ($lastKey === null) {
			return '';
		}

		$lastValue = $possibleValues[$lastKey];
		unset($possibleValues[$lastKey]);

		if ($possibleValues === []) {
			return $before . sprintf('Possible value is %s.', self::describeValue($lastValue));
		}

		$str = $before . sprintf(
			'Possible values are %s or %s.',
			implode(', ', array_map(fn(string $v): string => self::describeValue($v), $possibleValues)),
			self::describeValue($lastValue),
		);

		if (strlen($str) > $maxLength) {
			return '';
		}

		return $str;
	}

	/**
	 * @param list<string> $possibleValues
	 * @param string $value
	 */
	public static function getSuggestion(array $possibleValues, string $value): ?string
	{
		if ($possibleValues === []) {
			return null;
		}

		$best = null;
		$min = (strlen($value) / 4 + 1) * 10 + .1;
		foreach ($possibleValues as $item) {
			if ($value && ($len = levenshtein($item, $value, 10, 11, 10)) < $min) {
				$min = $len;
				$best = $item;
			}
		}

		return $best;
	}

	/**
	 * @param array<mixed> $items
	 * @return list<string>
	 */
	private static function selectOnlyStrings(array $items): array
	{
		$return = [];
		foreach ($items as $item) {
			if (is_string($item)) {
				$return[] = $item;
			}
		}

		return $return;
	}

}
