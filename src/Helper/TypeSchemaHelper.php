<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Helper;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Exception\UnsupportedTypeException;
use Shredio\TypeSchema\Issue\IssueCollection;
use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\TypeSchema;

final readonly class TypeSchemaHelper
{

	private const array SupportedBuiltinTypes = [
		'bool' => true,
		'float' => true,
		'int' => true,
		'mixed' => true,
		'null' => true,
		'object' => true,
		'string' => true,
	];

	public static function mergeErrors(IssueNode $firstNode, IssueNode $secondNode): IssueCollection
	{
		return new IssueCollection([$firstNode, $secondNode]);
	}

	/**
	 * @return Type<mixed>
	 *
	 * @throws UnsupportedTypeException
	 */
	public static function fromReflectionType(ReflectionType $type): Type
	{
		if ($type instanceof ReflectionNamedType) {
			if ($type->isBuiltin()) {
				$typeInstance = match ($type->getName()) {
					'int' => TypeSchema::get()->int(),
					'float' => TypeSchema::get()->float(),
					'string' => TypeSchema::get()->string(),
					'bool' => TypeSchema::get()->bool(),
					'object' => TypeSchema::get()->object(),
					'mixed' => TypeSchema::get()->mixed(),
					'null' => TypeSchema::get()->null(),
					default => throw new UnsupportedTypeException(sprintf('Unsupported builtin type: %s', $type->getName())),
				};
			} else {
				$typeInstance = TypeSchema::get()->mapper($type->getName()); // @phpstan-ignore-line
			}

			if ($type->allowsNull() && $type->getName() !== 'null') {
				$typeInstance = TypeSchema::get()->nullable($typeInstance);
			}

			return $typeInstance;
		}

		if ($type instanceof ReflectionUnionType) {
			$types = [];
			$isNullable = false;
			foreach ($type->getTypes() as $unionType) {
				if ($unionType instanceof ReflectionNamedType && $unionType->getName() === 'null') {
					$isNullable = true;
					continue;
				}

				$types[] = self::fromReflectionType($unionType);
			}

			$typeInstance = TypeSchema::get()->union($types); // @phpstan-ignore-line
			if ($isNullable) {
				$typeInstance = TypeSchema::get()->nullable($typeInstance);
			}

			return $typeInstance;
		}

		if ($type instanceof ReflectionIntersectionType) {
			throw new UnsupportedTypeException('Intersection types are not supported yet.');
		}

		throw new UnsupportedTypeException(sprintf('Unknown ReflectionType: %s', $type::class));
	}

	/**
	 * @return Type<mixed>
	 *
	 * @throws UnsupportedTypeException
	 */
	public static function fromStringType(string $type, bool $nullable = false): Type
	{
		if (!isset(self::SupportedBuiltinTypes[$type])) {
			if (!class_exists($type) && !enum_exists($type)) {
				throw new UnsupportedTypeException(sprintf('Unsupported type: %s', $type));
			}

			$typeObject = TypeSchema::get()->mapper($type);
		} else {
			if ($type === 'null') {
				return TypeSchema::get()->null();
			}

			$typeObject = match ($type) {
				'int' => TypeSchema::get()->int(),
				'float' => TypeSchema::get()->float(),
				'string' => TypeSchema::get()->string(),
				'bool' => TypeSchema::get()->bool(),
				'object' => TypeSchema::get()->object(),
				'mixed' => TypeSchema::get()->mixed(),
			};
		}

		if ($nullable) {
			$typeObject = TypeSchema::get()->nullable($typeObject);
		}

		return $typeObject;
	}

	/**
	 * @template TKey of array-key
	 * @template TValue
	 * @param array<array-key, array-key> $mapping oldKey => newKey
	 * @param Type<array<TKey, TValue>> $type
	 * @return Type<array<TKey, TValue>>
	 */
	public static function reindexShape(array $mapping, Type $type): Type
	{
		return TypeSchema::get()->before(
			static function (mixed $valueToParse, TypeContext $context) use ($mapping): mixed {
				$value = $context->conversionStrategy->array($valueToParse, true);
				if (!is_array($value)) {
					return $valueToParse;
				}

				foreach ($mapping as $oldKey => $newKey) {
					if (array_key_exists($oldKey, $value)) {
						$value[$newKey] = $value[$oldKey];
						unset($value[$oldKey]);
					} else {
						unset($value[$newKey]);
					}
				}

				return $value;
			},
			$type,
			static function (IssueNode $issues) use ($mapping): IssueNode {
				return self::reindexShapeFromIssues($mapping, $issues);
			},
		);
	}

	/**
	 * @param array<array-key, array-key> $mapping oldKey => newKey
	 */
	private static function reindexShapeFromIssues(array $mapping, IssueNode $issues): IssueNode
	{
		if ($issues instanceof IssueCollection) {
			$reversedMapping = array_flip($mapping);
			$newNodes = [];
			foreach ($issues->nodes as $childNode) {
				if ($childNode instanceof IssuePath) {
					$newNodes[] = self::reindexPath($childNode, $reversedMapping);
				} else {
					$newNodes[] = $childNode;
				}
			}

			return new IssueCollection($newNodes);
		}

		if ($issues instanceof IssuePath) {
			$reversedMapping = array_flip($mapping);
			return self::reindexPath($issues, $reversedMapping);
		}

		return $issues;
	}

	/**
	 * @param array<array-key, array-key> $reversedMapping newKey => oldKey
	 */
	private static function reindexPath(IssuePath $issuePath, array $reversedMapping): IssuePath
	{
		if (isset($reversedMapping[$issuePath->path->path])) {
			return $issuePath->withPath($issuePath->path->withPath($reversedMapping[$issuePath->path->path]));
		}

		return $issuePath;
	}

}
