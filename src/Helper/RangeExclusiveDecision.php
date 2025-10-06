<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Helper;

enum RangeExclusiveDecision
{

	case Ok;
	case ShouldBeGreater;
	case ShouldBeLess;

	/**
	 * @phpstan-assert-if-true self::Ok $this
	 */
	public function isOk(): bool
	{
		return $this === self::Ok;
	}

	public function isExclusive(): bool
	{
		return true;
	}

	public function isInclusive(): bool
	{
		return false;
	}

}
