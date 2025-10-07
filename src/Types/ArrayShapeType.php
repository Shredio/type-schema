<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\IdentifiedPath;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends Type<array<TKey, TValue>>
 */
final readonly class ArrayShapeType extends Type
{

	/** @var array<TKey, Type<TValue>> */
	private array $required;

	/** @var array<TKey, Type<TValue>> */
	private array $optional;

	/**
	 * @param array<TKey, Type<TValue>> $elements
	 * @param non-empty-string|null $identifier
	 */
	public function __construct(
		array $elements,
		private bool $allowExtraKeys = false,
		private ?string $identifier = null,
	)
	{
		$required = [];
		$optional = [];
		foreach ($elements as $key => $type) {
			if (!$type instanceof OptionalType) {
				$required[$key] = $type;
			} else {
				$optional[$key] = $type;
			}
		}

		$this->required = $required;
		$this->optional = $optional;
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		$value = $context->conversionStrategy->array($valueToParse, true);
		if ($value === null) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

		$return = [];
		$errors = [];
		$nestedContexts = $context->getNestedContexts();
		foreach ($this->required as $key => $type) {
			if (!array_key_exists($key, $value)) { // missing required key
				$error = $this->createChildError(
					$context->errorElementFactory->missingField($this->createDefinition($context)),
					$key,
					IdentifiedPath::create($this->identifier, $return),
				);

				if ($context->collectErrors) {
					$errors[] = $error;
				} else {
					return $error;
				}

				continue;
			}

			$ret = $type->parse($value[$key], $nestedContexts[$key] ?? $context);
			unset($value[$key]);
			if (!$ret instanceof ErrorElement) {
				$return[$key] = $ret;
			} else if ($context->collectErrors) { // error in the required key
				$errors[] = $this->createChildError($ret, $key, IdentifiedPath::create($this->identifier, $return));
			} else { // error in the required key
				return $this->createChildError($ret, $key, IdentifiedPath::create($this->identifier, $return));
			}
		}

		foreach ($value as $key => $val) {
			if (!isset($this->optional[$key])) {
				if (!$this->allowExtraKeys) {
					$error = $this->createChildError(
						$context->errorElementFactory->extraField($this->createDefinition($context)),
						$key,
						IdentifiedPath::create($this->identifier, $return),
					);

					if ($context->collectErrors) {
						$errors[] = $error;
					} else {
						return $error;
					}

					continue;
				}

				$return[$key] = $val;
				continue;
			}

			$ret = $this->optional[$key]->parse($val, $nestedContexts[$key] ?? $context);
			if (!$ret instanceof ErrorElement) {
				$return[$key] = $ret;
			} else if ($context->collectErrors) {
				$errors[] = $this->createChildError($ret, $key, IdentifiedPath::create($this->identifier, $return));
			} else {
				return $this->createChildError($ret, $key, IdentifiedPath::create($this->identifier, $return));
			}
		}

		if ($errors !== []) {
			return $this->createErrorCollection($errors);
		}

		return $return;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		$items = [];
		foreach ($this->required as $key => $type) {
			$items[] = new ArrayShapeItemNode($this->createTypeForKey($key), false, $type->getTypeNode($context));
		}
		foreach ($this->optional as $key => $type) {
			$items[] = new ArrayShapeItemNode($this->createTypeForKey($key), true, $type->getTypeNode($context));
		}

		return ArrayShapeNode::createSealed($items, ArrayShapeNode::KIND_ARRAY); // TODO: unsealed if allowExtraKeys
	}

	private function createTypeForKey(string|int $key): ConstExprIntegerNode|IdentifierTypeNode|ConstExprStringNode
	{
		return match (true) {
			is_int($key) => new ConstExprIntegerNode((string) $key),
			ctype_alpha($key) => new IdentifierTypeNode($key),
			ctype_alnum($key) && !preg_match('/^[0-9]/', $key) => new IdentifierTypeNode($key),
			default => new ConstExprStringNode((string) $key, ConstExprStringNode::SINGLE_QUOTED),
		};
	}

}
