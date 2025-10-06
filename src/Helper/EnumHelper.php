<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Helper;

use BackedEnum;

final readonly class EnumHelper
{

	public const int UnknownType = 0;
	public const int StringType = 1;
	public const int IntType = 2;

	/**
	 * @param class-string<BackedEnum> $backedEnum
	 */
	public static function getBackingValueType(string $backedEnum): int
	{
		$cases = $backedEnum::cases();
		if ($cases === []) {
			return self::UnknownType;
		}

		return is_string($cases[0]->value) ? self::StringType : self::IntType;
	}

	/**
	 * @template T of BackedEnum
	 * @param class-string<T> $backedEnum
	 * @return T|null
	 */
	public static function createStringEnum(string $backedEnum, string $value): ?BackedEnum
	{
		return $backedEnum::tryFrom($value);
	}

	/**
	 * @param class-string<BackedEnum> $backedEnum
	 */
	public static function getCorrectTypeForBackedEnum(mixed $value, string $backedEnum): string|int|null
	{
		$cases = $backedEnum::cases();
		if ($cases === []) {
			return null;
		}

		if (is_string($cases[0]->value)) {
			return is_string($value) ? $value : null;
		} else {
			return is_int($value) ? $value : null;
		}
	}

}
