<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Helper;

use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorCollection;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorPath;
use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\TypeSchema;

final readonly class TypeSchemaHelper
{

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
