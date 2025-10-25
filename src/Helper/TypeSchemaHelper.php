<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Helper;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorCollection;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorPath;
use Shredio\TypeSchema\Exception\UnsupportedTypeException;
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
			static function (ErrorElement $error) use ($mapping): ErrorElement {
				return self::reindexShapeFromErrors($mapping, $error);
			},
		);
	}

	/**
	 * @param array<array-key, array-key> $mapping oldKey => newKey
	 */
	private static function reindexShapeFromErrors(array $mapping, ErrorElement $error): ErrorElement
	{
		if ($error instanceof ErrorCollection) {
			$reversedMapping = array_flip($mapping);
			$newCollection = [];
			foreach ($error->collection as $childError) {
				if ($childError instanceof ErrorPath) {
					$newCollection[] = self::reindexPath($childError, $reversedMapping);
				} else{
					$newCollection[] = $childError;
				}
			}

			return new ErrorCollection($newCollection);
		}

		if ($error instanceof ErrorPath) {
			$reversedMapping = array_flip($mapping);
			return self::reindexPath($error, $reversedMapping);
		}

		return $error;
	}

	/**
	 * @param array<array-key, array-key> $reversedMapping newKey => oldKey
	 */
	private static function reindexPath(ErrorPath $error, array $reversedMapping): ErrorPath
	{
		if (isset($reversedMapping[$error->path->path])) {
			return $error->withPath($error->path->withPath($reversedMapping[$error->path->path]));
		}

		return $error;
	}

}
