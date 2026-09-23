<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Issue\EmptyValue;
use Shredio\TypeSchema\Result\Failure;

/**
 * @extends DecorateType<non-empty-string, string>
 */
final readonly class NonEmptyStringType extends DecorateType
{

	protected function decorate(mixed $value, TypeContext $context): Failure|string
	{
		if ($value === '') {
			return new Failure(new EmptyValue($value));
		}

		return $value;
	}

	protected function getInnerType(): StringType
	{
		return new StringType();
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode('non-empty-string');
	}

}
