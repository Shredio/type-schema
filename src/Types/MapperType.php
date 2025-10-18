<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Exception\ClassMapperNotFoundException;

/**
 * @template T of object
 * @extends Type<T>
 */
final readonly class MapperType extends Type
{

	/**
	 * @param class-string<T> $className
	 */
	public function __construct(
		private string $className,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		$mapper = $context->classMapperProvider->provide($this->className);
		if ($mapper === null) {
			throw new ClassMapperNotFoundException($this->className);
		}

		return $mapper->parse($valueToParse, $context);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		$mapper = $context->classMapperProvider->provide($this->className);
		if ($mapper === null) {
			throw new ClassMapperNotFoundException($this->className);
		}

		return $mapper->getTypeNode($context);
	}

}
