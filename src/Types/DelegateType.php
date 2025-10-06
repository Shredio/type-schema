<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;

/**
 * @template T
 * @extends Type<T>
 */
abstract readonly class DelegateType extends Type
{

	/**
	 * @return Type<T>
	 */
	abstract protected function getCoreType(): Type;

	final public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		return $this->getCoreType()->parse($valueToParse, $context);
	}

	final protected function getTypeNode(TypeContext $context): TypeNode
	{
		return $this->getCoreType()->getTypeNode($context);
	}

}
