<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Result\Failure;

/**
 * @extends Type<bool>
 */
final readonly class BoolType extends Type
{

	public function parse(mixed $valueToParse, TypeContext $context): Failure|bool
	{
		$value = $context->conversionStrategy->bool($valueToParse);
		if ($value === null) {
			return $this->createInvalidTypeFailure($valueToParse, $context);
		}

		return $value;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode('bool');
	}

}
