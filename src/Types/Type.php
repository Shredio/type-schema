<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Conversion\ConfigurableConversionStrategy;
use Shredio\TypeSchema\Conversion\ConversionStrategyDelegator;
use Shredio\TypeSchema\Conversion\Converter\Array\ArrayConverter;
use Shredio\TypeSchema\Conversion\Converter\Bool\BoolConverter;
use Shredio\TypeSchema\Conversion\Converter\Null\NullConverter;
use Shredio\TypeSchema\Conversion\Converter\Number\NumberConverter;
use Shredio\TypeSchema\Conversion\Converter\String\StringConverter;
use Shredio\TypeSchema\Error\ErrorCollection;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorPath;
use Shredio\TypeSchema\Error\IdentifiedPath;
use Shredio\TypeSchema\Error\Path;

/**
 * @template-covariant T
 */
abstract readonly class Type
{

	/**
	 * @param TypeContext $context
	 * @return T|ErrorElement
	 */
	abstract public function parse(mixed $valueToParse, TypeContext $context): mixed;

	abstract protected function getTypeNode(TypeContext $context): TypeNode;

	final protected function createDefinition(TypeContext $context): TypeDefinition
	{
		return new TypeDefinition(fn (): TypeNode => $this->getTypeNode($context));
	}

	/**
	 * @template U
	 * @param callable(T): U $callback
	 * @return Type<U>
	 */
	final public function after(callable $callback): Type
	{
		return new AfterType($this, $callback);
	}

	/**
	 * @return Type<T>
	 */
	final public function conversion(
		?StringConverter $string = null,
		?NumberConverter $int = null,
		?NumberConverter $float = null,
		?BoolConverter $bool = null,
		?NullConverter $null = null,
		?ArrayConverter $array = null,
	): Type
	{
		return new ContextType($this, static fn (TypeContext $context): TypeContext => $context->withConversionStrategy(
			new ConversionStrategyDelegator(
				string: $string === null ? $context->conversionStrategy : ConfigurableConversionStrategy::forString($string),
				int: $int === null ? $context->conversionStrategy : ConfigurableConversionStrategy::forNumber($int),
				float: $float === null ? $context->conversionStrategy : ConfigurableConversionStrategy::forNumber($float),
				bool: $bool === null ? $context->conversionStrategy : ConfigurableConversionStrategy::forBool($bool),
				null: $null === null ? $context->conversionStrategy : ConfigurableConversionStrategy::forNull($null),
				array: $array === null ? $context->conversionStrategy : ConfigurableConversionStrategy::forArray($array),
				object: $context->conversionStrategy,
			)),
		);
	}

	/**
	 * @param callable(T $value, TypeContext $context): ?ErrorElement $callback
	 * @return Type<T>
	 */
	final public function validate(callable $callback): Type
	{
		return new ValidateType($this, $callback);
	}

	/**
	 * @phpstan-assert-if-true ErrorElement $value
	 */
	protected function isError(mixed $value): bool
	{
		return $value instanceof ErrorElement;
	}

	/**
	 * @param non-empty-list<ErrorElement> $errors
	 */
	final protected function createErrorCollection(array $errors): ErrorElement
	{
		return !isset($errors[1]) ? $errors[0] : new ErrorCollection($errors);
	}

	final protected function createChildError(
		ErrorElement $error,
		string|int $path,
		?IdentifiedPath $identified = null,
	): ErrorElement
	{
		return new ErrorPath($error, new Path($path, $identified));
	}

}
