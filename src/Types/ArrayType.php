<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends Type<array<TKey, TValue>>
 */
final readonly class ArrayType extends Type
{

	/**
	 * @param Type<TKey> $keyType
	 * @param Type<TValue> $valueType
	 */
	public function __construct(
		private Type $keyType,
		private Type $valueType,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): ErrorElement|array
	{
		$value = $context->conversionStrategy->array($valueToParse, true);
		if ($value === null) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

		$checkKey = !$this->keyType instanceof ArrayKeyType; // optimization, no need to check keys if they are array-keys
		$return = [];
		$errors = [];
		foreach ($value as $key => $item) {
			// Key
			if ($checkKey) {
				$parsedKey = $this->keyType->parse($key, $context);
				if ($parsedKey instanceof ErrorElement) {
					$error = $this->createChildError($parsedKey, $key);
					if ($context->collectErrors) {
						$errors[] = $error;
						continue;
					}

					return $error;
				}
			} else {
				$parsedKey = $key;
			}

			// Value
			$parsedValue = $this->valueType->parse($item, $context);
			if ($parsedValue instanceof ErrorElement) {
				$error = $this->createChildError($parsedValue, $key);
				if ($context->collectErrors) {
					$errors[] = $error;
				} else {
					return $error;
				}

				continue;
			}

			$return[$parsedKey] = $parsedValue;
		}

		if ($errors !== []) {
			return $this->createErrorCollection($errors);
		}

		/** @var array<TKey, TValue> */
		return $return;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new GenericTypeNode(new IdentifierTypeNode('array'), [
			$this->keyType->getTypeNode($context),
			$this->valueType->getTypeNode($context),
		]);
	}

}
