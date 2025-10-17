<?php declare(strict_types = 1);

namespace Shredio\TypeSchema;

use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\Types\UnionType;

class TypeSchema
{

	private static ?TypeSchema $instance = null;

	/**
	 * @return Type<int>
	 */
	public function int(): Type
	{
		return new Types\IntType();
	}

	/**
	 * @return Type<string>
	 */
	public function string(): Type
	{
		return new Types\StringType();
	}

	/**
	 * @template T
	 * @param Type<T> $type
	 * @param list<mixed> $nullValues
	 * @return Type<T|null>
	 */
	public function nullable(Type $type, array $nullValues = []): Type
	{
		return new Types\NullableType($type, $nullValues);
	}

	/**
	 * @return Type<int>
	 */
	public function intRange(?int $min, ?int $max): Type
	{
		return new Types\IntRangeType($min, $max);
	}

	/**
	 * @return Type<bool>
	 */
	public function bool(): Type
	{
		return new Types\BoolType();
	}

	/**
	 * @return Type<float>
	 */
	public function float(): Type
	{
		return new Types\FloatType();
	}

	/**
	 * @template T of object=object
	 * @param class-string<T>|null $class
	 * @return Type<T>
	 */
	public function object(?string $class = null): Type
	{
		return new Types\ObjectType($class); // @phpstan-ignore return.type
	}

	/**
	 * @template T
	 * @param non-empty-list<Type<T>> $types
	 * @return Type<T>
	 */
	public function union(array $types): Type
	{
		if (count($types) === 1) {
			return $types[0];
		}

		return new UnionType($types);
	}

	/**
	 * @template TKey of array-key
	 * @template TValue
	 * @param array<TKey, Type<TValue>> $schema
	 * @param non-empty-string|null $identifier
	 * @return Type<array<TKey, TValue>>
	 */
	public function arrayShape(array $schema, bool $allowExtraItems = false, ?string $identifier = null): Type
	{
		return new Types\ArrayShapeType($schema, $allowExtraItems, $identifier);
	}

	/**
	 * @template-covariant T
	 * @param Type<T> $itemType
	 * @return Type<non-empty-list<T>>
	 */
	public function nonEmptyList(Type $itemType): Type // @phpstan-ignore method.variance
	{
		return new Types\NonEmptyListType($itemType);
	}

	/**
	 * @template-covariant T
	 * @param Type<T> $itemType
	 * @return Type<list<T>>
	 */
	public function list(Type $itemType): Type // @phpstan-ignore method.variance
	{
		return new Types\ListType($itemType);
	}

	/**
	 * @template TKey of array-key
	 * @template-covariant TValue
	 * @param Type<TKey> $keyType
	 * @param Type<TValue> $valueType
	 * @return Type<array<TKey, TValue>>
	 */
	public function array(Type $keyType, Type $valueType): Type
	{
		return new Types\ArrayType($keyType, $valueType);
	}

	/**
	 * @return Type<non-empty-string>
	 */
	public function nonEmptyString(): Type
	{
		return new Types\NonEmptyStringType();
	}

	/**
	 * @return Type<array-key>
	 */
	public function arrayKey(): Type
	{
		return new Types\ArrayKeyType();
	}

	/**
	 * @return Type<mixed>
	 */
	public function mixed(): Type
	{
		return new Types\MixedType();
	}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return Type<T>
	 */
	public function mapper(string $className): Type
	{
		return new Types\MapperType($className);
	}

	/**
	 * @template T
	 * @param Type<T> $type
	 * @return Type<T>
	 */
	public function optional(Type $type): Type
	{
		return new Types\OptionalType($type);
	}

	final public static function get(): static
	{
		/** @var static */
		return self::$instance ??= new static();
	}

	final protected function __construct()
	{
	}

}
