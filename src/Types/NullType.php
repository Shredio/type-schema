<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Result\Failure;

/**
 * @extends Type<null>
 */
final readonly class NullType extends Type
{

	public function parse(mixed $valueToParse, TypeContext $context): ?Failure
	{
		$value = $context->conversionStrategy->null($valueToParse);
		if ($value !== null) {
			return $this->createInvalidTypeFailure($valueToParse, $context);
		}

		return null;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode('null');
	}

}
