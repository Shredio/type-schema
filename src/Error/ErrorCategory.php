<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

enum ErrorCategory
{

	/**
	 * The input cannot be assembled into the target type at all.
	 * Typically indicates a bug in the caller: wrong field type, missing required field,
	 * unexpected field in a closed shape. End users cannot fix these by editing form values.
	 */
	case Structural;

	/**
	 * The input has the right shape but its content violates a constraint
	 * (range, length, allowed values, format, ...). These map to user-facing
	 * form validation messages.
	 */
	case Validation;

	/**
	 * Resolves the overall category for a set of reports.
	 *
	 * A single Structural report takes precedence: if any report is Structural,
	 * the whole set is Structural (typically mapped to HTTP 400), otherwise Validation (422).
	 * An empty set is treated as Validation.
	 *
	 * @param iterable<ErrorReport> $reports
	 */
	public static function resolve(iterable $reports): self
	{
		foreach ($reports as $report) {
			if ($report->category === self::Structural) {
				return self::Structural;
			}
		}

		return self::Validation;
	}

	/**
	 * @param iterable<ErrorReport> $reports
	 */
	public static function hasStructural(iterable $reports): bool
	{
		return self::resolve($reports) === self::Structural;
	}

}
