<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeUnsealedTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\IdentifiedPath;
use Shredio\TypeSchema\Issue\MissingKey;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\IssueCollector;
use Shredio\TypeSchema\Result\WithNotices;

/**
 * Keys not defined in the shape are removed and reported as ExtraKey notices, unless a rest type makes the shape open.
 *
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
	 * @param Type<mixed>|null $rest type of values under keys not defined in the shape, null for a closed shape
	 * @param non-empty-string|null $identifier
	 */
	public function __construct(
		array $elements,
		private ?Type $rest = null,
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
			return $this->createInvalidTypeFailure($valueToParse, $context);
		}

		$return = [];
		$issues = null;
		$nestedContexts = $context->getNestedContexts();
		foreach ($this->required as $key => $type) {
			if (!array_key_exists($key, $value)) {
				$issues ??= new IssueCollector();
				$issues->addError(new MissingKey(), $key, IdentifiedPath::create($this->identifier, $return));
				if (!$context->collectErrors) {
					return $issues->createFailure();
				}

				continue;
			}

			$ret = $type->parse($value[$key], $nestedContexts[$key] ?? $context);
			unset($value[$key]);
			if ($ret instanceof Failure) {
				$issues ??= new IssueCollector();
				$issues->addChild($ret, $key, IdentifiedPath::create($this->identifier, $return));
				if (!$context->collectErrors) {
					return $issues->createFailure();
				}

				continue;
			}

			if ($ret instanceof WithNotices) {
				$issues ??= new IssueCollector();
				$issues->addChild($ret, $key, IdentifiedPath::create($this->identifier, $return));
				$ret = $ret->value;
			}

			$return[$key] = $ret;
		}

		foreach ($value as $key => $val) {
			$type = $this->optional[$key] ?? $this->rest;
			if ($type === null) {
				$issues ??= new IssueCollector();
				$issues->addNotice(new ExtraKey(), $key, IdentifiedPath::create($this->identifier, $return));

				continue;
			}

			$ret = $type->parse($val, $nestedContexts[$key] ?? $context);
			if ($ret instanceof Failure) {
				$issues ??= new IssueCollector();
				$issues->addChild($ret, $key, IdentifiedPath::create($this->identifier, $return));
				if (!$context->collectErrors) {
					return $issues->createFailure();
				}

				continue;
			}

			if ($ret instanceof WithNotices) {
				$issues ??= new IssueCollector();
				$issues->addChild($ret, $key, IdentifiedPath::create($this->identifier, $return));
				$ret = $ret->value;
			}

			$return[$key] = $ret;
		}

		/** @var array<TKey, TValue> $parsedValue */
		$parsedValue = $return;

		return $issues === null ? $parsedValue : $issues->createResult($parsedValue);
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

		if ($this->rest === null) {
			return ArrayShapeNode::createSealed($items, ArrayShapeNode::KIND_ARRAY);
		}

		return ArrayShapeNode::createUnsealed(
			$items,
			new ArrayShapeUnsealedTypeNode($this->rest->getTypeNode($context), null),
			ArrayShapeNode::KIND_ARRAY,
		);
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
