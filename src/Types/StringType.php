<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Result\Failure;

/**
 * @extends Type<string>
 */
final readonly class StringType extends Type
{

	public function parse(mixed $valueToParse, TypeContext $context): string|Failure
	{
		$value = $context->conversionStrategy->string($valueToParse);
		if ($value === null) {
			return $this->createInvalidTypeFailure($valueToParse, $context);
		}

		return $value;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode('string');
	}

}
