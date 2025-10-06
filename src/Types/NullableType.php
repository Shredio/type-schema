<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;

/**
 * @template T
 * @extends Type<T|null>
 */
final readonly class NullableType extends Type
{

	/**
	 * @param Type<T> $type
	 */
	public function __construct(
		private Type $type,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		if ($valueToParse === null) {
			return null;
		}

		return $this->type->parse($valueToParse, $context);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new NullableTypeNode($this->type->getTypeNode($context));
	}

}
