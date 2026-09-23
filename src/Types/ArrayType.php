<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\IssueCollector;
use Shredio\TypeSchema\Result\WithNotices;

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

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		$value = $context->conversionStrategy->array($valueToParse, true);
		if ($value === null) {
			return $this->createInvalidTypeFailure($valueToParse, $context);
		}

		$checkKey = !$this->keyType instanceof ArrayKeyType; // optimization, no need to check keys if they are array-keys
		$return = [];
		$issues = null;
		foreach ($value as $key => $item) {
			// Key
			if ($checkKey) {
				$parsedKey = $this->keyType->parse($key, $context);
				if ($parsedKey instanceof Failure) {
					$issues ??= new IssueCollector();
					$issues->addChild($parsedKey, $key);
					if (!$context->collectErrors) {
						return $issues->createFailure();
					}

					continue;
				}

				if ($parsedKey instanceof WithNotices) {
					$issues ??= new IssueCollector();
					$issues->addChild($parsedKey, $key);
					$parsedKey = $parsedKey->value;
				}
			} else {
				$parsedKey = $key;
			}

			// Value
			$parsedValue = $this->valueType->parse($item, $context);
			if ($parsedValue instanceof Failure) {
				$issues ??= new IssueCollector();
				$issues->addChild($parsedValue, $key);
				if (!$context->collectErrors) {
					return $issues->createFailure();
				}

				continue;
			}

			if ($parsedValue instanceof WithNotices) {
				$issues ??= new IssueCollector();
				$issues->addChild($parsedValue, $key);
				$parsedValue = $parsedValue->value;
			}

			/** @var array-key $parsedKey */
			$return[$parsedKey] = $parsedValue;
		}

		/** @var array<TKey, TValue> $parsedValue */
		$parsedValue = $return;

		return $issues === null ? $parsedValue : $issues->createResult($parsedValue);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new GenericTypeNode(new IdentifierTypeNode('array'), [
			$this->keyType->getTypeNode($context),
			$this->valueType->getTypeNode($context),
		]);
	}

}
