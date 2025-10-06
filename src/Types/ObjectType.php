<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;

/**
 * @template-covariant T of object=object
 * @extends Type<object>
 */
final readonly class ObjectType extends Type
{

	/**
	 * @param class-string<T>|null $name
	 */
	public function __construct(
		private ?string $name = null,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		if (!is_object($valueToParse)) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

		if ($this->name !== null && !is_a($valueToParse, $this->name)) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

		return $valueToParse;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode($this->name === null ? 'object' : $this->name);
	}

}
