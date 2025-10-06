<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;

/**
 * @template T
 * @extends Type<list<T>>
 */
final readonly class ListType extends Type
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

		/** @var list<T> */
		return $return;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new GenericTypeNode(new IdentifierTypeNode('list'), [
			$this->itemType->getTypeNode($context),
		]);
	}

}
