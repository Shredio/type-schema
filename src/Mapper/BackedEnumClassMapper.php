<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use BackedEnum;
use ReflectionEnum;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Helper\EnumHelper;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\NotAllowedValue;
use Shredio\TypeSchema\Result\Failure;

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

	public function create(string $className, mixed $valueToParse, TypeContext $context): BackedEnum|Failure
	{
		$backingValueType = EnumHelper::getBackingValueType($className);
		if ($backingValueType === EnumHelper::UnknownType) {
			$reflection = new ReflectionEnum($className);
			$backingType = $reflection->getBackingType()?->getName();
			if ($backingType === '' || $backingType === null) {
				$backingType = 'string'; // This should not happen
			}

			$def = $this->createNamedDefinition($backingType);
			return new Failure(new InvalidType($def, $valueToParse));
		}

		if ($backingValueType === EnumHelper::StringType) {
			$value = $context->conversionStrategy->string($valueToParse);
			if ($value === null) {
				return new Failure(new InvalidType($this->createNamedDefinition('string'), $valueToParse));
			}

			$backedEnum = $className::tryFrom($value);
			if ($backedEnum === null) {
				return new Failure(new NotAllowedValue($value, array_map(
					fn (BackedEnum $case): int|string => $case->value,
					$className::cases(),
				)));
			}

			return $backedEnum;
		}

		if ($backingValueType === EnumHelper::IntType) {
			$value = $context->conversionStrategy->int($valueToParse);
			if ($value === null) {
				return new Failure(new InvalidType($this->createNamedDefinition('int'), $valueToParse));
			}

			$backedEnum = $className::tryFrom($value);
			if ($backedEnum === null) {
				return new Failure(new NotAllowedValue($value, array_map(
					fn (BackedEnum $case): int|string => $case->value,
					$className::cases(),
				)));
			}

			return $backedEnum;
		}

		throw new LogicException(sprintf('Invalid BackedEnum %s.', $className)); // this should not happen
	}

}
