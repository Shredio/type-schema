<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;

/**
 * @extends Type<float>
 */
final readonly class FloatType extends Type
{

	public function __construct(
		private bool $allowInf = true,
		private bool $allowNan = false,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): ErrorElement|float
	{
		$value = $context->conversionStrategy->float($valueToParse);
		if ($value === null) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

		if (!$this->allowNan && is_nan($value)) {
			return $context->errorElementFactory->invalidValue($this->createDefinition($context), $value, 'NaN values are not allowed');
		}

		if (!$this->allowInf && is_infinite($value)) {
			return $context->errorElementFactory->invalidValue($this->createDefinition($context), $value, 'Infinite values are not allowed');
		}

		return $value;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode('float');
	}

}
