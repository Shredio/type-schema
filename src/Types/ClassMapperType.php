<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Mapper\ClassMapper;

/**
 * @template T of object
 * @extends Type<T>
 */
final readonly class ClassMapperType extends Type
{

	/**
	 * @param class-string<T> $className
	 * @param ClassMapper<T> $mapper
	 */
	public function __construct(
		private string $className,
		private ClassMapper $mapper,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		if ($valueToParse instanceof $this->className) {
			return $valueToParse;
		}

		if ($context->conversionStrategy->isStrictForObject($this->className)) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

		return $this->mapper->create($this->className, $valueToParse, $context);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode($this->className);
	}

}
