<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Helper;

enum RangeInclusiveDecision
{

	case Ok;
	case ShouldBeGreaterOrEqual;
	case ShouldBeLessOrEqual;

	/**
	 * @phpstan-assert-if-true self::Ok $this
	 */
	public function isOk(): bool
	{
		return $this === self::Ok;
	}

	public function isExclusive(): bool
	{
		return false;
	}

	public function isInclusive(): bool
	{
		return true;
	}

}
