<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use BackedEnum;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Helper\EnumHelper;

/**
 * @template T of BackedEnum
 * @extends ClassMapper<T>
 */
final readonly class BackedEnumClassMapper extends ClassMapper
{

	public function isSupported(string $className): bool
	{
		return is_subclass_of($className, BackedEnum::class);
	}

	public function create(string $className, mixed $valueToParse, TypeContext $context): BackedEnum|ErrorElement
	{
		$backingValueType = EnumHelper::getBackingValueType($className);
		if ($backingValueType === EnumHelper::UnknownType) {
			return $context->errorElementFactory->invalidType($this->createDefinition($className), $valueToParse);
		}

		if ($backingValueType === EnumHelper::StringType) {
			$value = $context->conversionStrategy->string($valueToParse);
			if ($value === null) {
				return $context->errorElementFactory->invalidType($this->createDefinition($className), $valueToParse);
			}

			$backedEnum = $className::tryFrom($value);
			if ($backedEnum === null) {
				return $context->errorElementFactory->valueNotInAllowedValues($this->createDefinition($className), $value, array_map(
					fn (BackedEnum $case): int|string => $case->value,
					$className::cases(),
				));
			}

			return $backedEnum;
		}

		if ($backingValueType === EnumHelper::IntType) {
			$value = $context->conversionStrategy->int($valueToParse);
			if ($value === null) {
				return $context->errorElementFactory->invalidType($this->createDefinition($className), $valueToParse);
			}

			$backedEnum = $className::tryFrom($value);
			if ($backedEnum === null) {
				return $context->errorElementFactory->valueNotInAllowedValues($this->createDefinition($className), $value, array_map(
					fn (BackedEnum $case): int|string => $case->value,
					$className::cases(),
				));
			}

			return $backedEnum;
		}

		return $context->errorElementFactory->invalidType($this->createDefinition($className), $valueToParse);
	}

}
