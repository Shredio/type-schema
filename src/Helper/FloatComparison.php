<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Helper;

use Shredio\TypeSchema\Exception\LogicException;

/**
 * Compares floats with a tolerance, so values differing only by a rounding error (e.g. 0.1 + 0.2 and 0.3) are equal.
 *
 * @internal
 */
final readonly class FloatComparison
{

	private const float Epsilon = 1e-10;

	public static function isLessThan(float $a, float $b): bool
	{
		return self::compare($a, $b) < 0;
	}

	public static function isLessThanOrEqualTo(float $a, float $b): bool
	{
		return self::compare($a, $b) <= 0;
	}

	public static function isGreaterThan(float $a, float $b): bool
	{
		return self::compare($a, $b) > 0;
	}

	public static function isGreaterThanOrEqualTo(float $a, float $b): bool
	{
		return self::compare($a, $b) >= 0;
	}

	/**
	 * @return int<-1, 1> -1 when $a < $b, 0 when they are equal, 1 when $a > $b
	 */
	public static function compare(float $a, float $b): int
	{
		if (is_nan($a) || is_nan($b)) {
			throw new LogicException('Trying to compare NAN.');
		}

		if (!is_finite($a) && !is_finite($b) && $a === $b) {
			return 0;
		}

		$difference = abs($a - $b);
		if ($difference < self::Epsilon || $difference / max(abs($a), abs($b)) < self::Epsilon) {
			return 0;
		}

		return $a < $b ? -1 : 1;
	}

}
