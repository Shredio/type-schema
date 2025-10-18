<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;

/**
 * @template T
 * @extends Type<T>
 */
final readonly class BeforeType extends Type
{

	/** @var callable(mixed $valueToParse, TypeContext $context): mixed */
	private mixed $fn;

	/** @var (callable(ErrorElement $error): ErrorElement)|null */
	private mixed $onError;

	/**
	 * @no-named-arguments
	 * @param callable(mixed $valueToParse, TypeContext $context): mixed $fn
	 * @param Type<T> $innerType
	 * @param (callable(ErrorElement $error): ErrorElement)|null $onError
	 */
	public function __construct(
		callable $fn,
		private Type $innerType,
		?callable $onError = null,
	)
	{
		$this->fn = $fn;
		$this->onError = $onError;
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		$valueToParse = ($this->fn)($valueToParse, $context);
		$result = $this->innerType->parse($valueToParse, $context);
		if ($this->onError !== null && $result instanceof ErrorElement) {
			return ($this->onError)($result);
		}

		return $result;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return $this->innerType->getTypeNode($context);
	}

}
