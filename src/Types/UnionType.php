<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Shredio\TypeSchema\Context\TypeContext;

/**
 * @extends Type<mixed>
 */
final readonly class UnionType extends Type
{

	/**
	 * @param non-empty-list<Type<mixed>> $types
	 */
	public function __construct(
		private array $types,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		foreach ($this->types as $type) {
			$val = $type->parse($valueToParse, $context);
			if (!$this->isError($val)) {
				return $val;
			}
		}

		return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new UnionTypeNode(array_map(fn (Type $type): TypeNode => $type->getTypeNode($context), $this->types));
	}

}
