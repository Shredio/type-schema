<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use BackedEnum;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Helper\EnumHelper;
use Shredio\TypeSchema\Types\Type;

/**
 * @template T of BackedEnum
 * @extends Type<T>
 */
final readonly class BackedEnumMapper extends Type
{

	/**
	 * @param class-string<T> $enumClass
	 */
	public function __construct(
		public string $enumClass,
	) {
	}

	public function parse(mixed $valueToParse, TypeContext $context): BackedEnum|ErrorElement
	{
		if ($valueToParse instanceof $this->enumClass) {
			return $valueToParse;
		}

		if ($context->conversionStrategy->isStrictForObject(BackedEnum::class)) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

		$backingValueType = EnumHelper::getBackingValueType($this->enumClass);
		if ($backingValueType === EnumHelper::UnknownType) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

		if ($backingValueType === EnumHelper::StringType) {
			$value = $context->conversionStrategy->string($valueToParse);
			if ($value === null) {
				return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
			}

			$backedEnum = $this->enumClass::tryFrom($value);
			if ($backedEnum === null) {
				return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse); // TODO: invalidCase instead of invalidType
			}

			return $backedEnum;
		}

		if ($backingValueType === EnumHelper::IntType) {
			$value = $context->conversionStrategy->int($valueToParse);
			if ($value === null) {
				return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
			}

			$backedEnum = $this->enumClass::tryFrom($value);
			if ($backedEnum === null) {
				return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse); // TODO: invalidCase instead of invalidType
			}

			return $backedEnum;
		}

		return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode($this->enumClass);
	}

}
