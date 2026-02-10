<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Context\TypeDefinition;
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
