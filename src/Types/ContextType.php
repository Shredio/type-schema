<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;

/**
 * @internal
 * @template T
 * @extends Type<T>
 */
final readonly class ContextType extends Type
{

	/** @var callable(TypeContext): TypeContext */
	private mixed $contextModifier;

	/**
	 * @param Type<T> $innerType
	 * @param callable(TypeContext): TypeContext $contextModifier
	 */
	public function __construct(
		private Type $innerType,
		callable $contextModifier,
	)
	{
		$this->contextModifier = $contextModifier;
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		return $this->innerType->parse($valueToParse, ($this->contextModifier)($context));
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return $this->innerType->getTypeNode($context);
	}

}
