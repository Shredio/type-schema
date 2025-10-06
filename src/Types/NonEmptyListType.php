<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;

/**
 * @template T
 * @extends Type<non-empty-list<T>>
 */
final readonly class NonEmptyListType extends Type
{

	/**
	 * @param Type<T> $itemType
	 */
	public function __construct(
		private Type $itemType,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): ErrorElement|array
	{
		$value = $context->conversionStrategy->array($valueToParse, true);
		if ($value === null) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

		if ($value === []) {
			return $context->errorElementFactory->itemCountRange(
				$this->createDefinition($context),
				0,
				NumberInclusiveRange::fromInts(1),
				RangeInclusiveDecision::ShouldBeGreaterOrEqual,
			);
		}

		$return = [];
		$errors = [];
		$expectedKey = 0;
		foreach ($value as $key => $item) {
			if ($key !== $expectedKey) {
				return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
			}

			$elementValue = $this->itemType->parse($item, $context);
			if (!$elementValue instanceof ErrorElement) {
				$return[] = $elementValue;
			} else if ($context->collectErrors) {
				$errors[] = $this->createChildError($elementValue, $key);
			} else {
				return $this->createChildError($elementValue, $key);
			}

			$expectedKey++;
		}

		if ($errors !== []) {
			return $this->createErrorCollection($errors);
		}

		/** @var non-empty-list<T> */
		return $return;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new GenericTypeNode(new IdentifierTypeNode('non-empty-list'), [
			$this->itemType->getTypeNode($context),
		]);
	}

}
