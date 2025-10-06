<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;

/**
 * @extends Type<string>
 */
final readonly class StringType extends Type
{

	public function parse(mixed $valueToParse, TypeContext $context): string|ErrorElement
	{
		$value = $context->conversionStrategy->string($valueToParse);
		if ($value === null) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

		return $value;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode('string');
	}

}
