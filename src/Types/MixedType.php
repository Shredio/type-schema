<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;

/**
 * @extends Type<mixed>
 */
final readonly class MixedType extends Type
{

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		return $valueToParse;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode('mixed');
	}

}
