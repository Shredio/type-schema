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
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\WithNotices;

/**
 * @template-covariant T
 */
abstract readonly class Type
{

	/**
	 * Returns the parsed value, a Failure, or WithNotices when the value was parsed but has notices.
	 * A clean value is returned as is, without any wrapper.
	 *
	 * @return T|Failure|WithNotices<T>
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
	 * @param callable(T $value, TypeContext $context): ?IssueNode $callback
	 * @return Type<T>
	 */
	final public function validate(callable $callback): Type
	{
		return new ValidateType($this, $callback);
	}

	/**
	 * @phpstan-assert-if-true Failure $value
	 */
	protected function isError(mixed $value): bool
	{
		return $value instanceof Failure;
	}

	final protected function createInvalidTypeFailure(mixed $value, TypeContext $context): Failure
	{
		return new Failure(new InvalidType($this->createDefinition($context), $value));
	}

	/**
	 * When the whole value has an invalid type (not one of its children), reports this type as the expected one
	 * instead of the type of the inner type this type delegates to.
	 */
	final protected function withOwnDefinition(Failure $failure, TypeContext $context): Failure
	{
		if (!$failure->errors instanceof InvalidType) {
			return $failure;
		}

		return new Failure($failure->errors->withDefinition($this->createDefinition($context)), $failure->notices);
	}

}
