<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;

/**
 * @extends DecorateType<int, int>
 */
final readonly class IntRangeType extends DecorateType
{

	private NumberInclusiveRange $range;

	public function __construct(?int $min, ?int $max)
	{
		$this->range = NumberInclusiveRange::fromInts($min, $max);
	}

	protected function decorate(mixed $value, TypeContext $context): ErrorElement|int
	{
		$decided = $this->range->decide($value);
		if ($decided->isOk()) {
			return $value;
		}

		return $context->errorElementFactory->numberRange($this->createDefinition($context), $value, $this->range, $decided);
	}

	protected function getInnerType(): IntType
	{
		return new IntType();
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new GenericTypeNode(new IdentifierTypeNode('int'), $this->range->getTypeGenericNodes());
	}

}
