<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Error\ErrorElement;

/**
 * @template T of object
 */
abstract readonly class ClassMapper
{

	/**
	 * @param class-string $className
	 */
	abstract public function isSupported(string $className): bool;

	/**
	 * @param class-string<T> $className
	 * @return T|ErrorElement
	 */
	abstract public function create(string $className, mixed $valueToParse, TypeContext $context): object;

	final protected function createDefinition(TypeNode $typeNode): TypeDefinition
	{
		return new TypeDefinition($typeNode);
	}

	final protected function createNamedDefinition(string $name): TypeDefinition
	{
		return new TypeDefinition(new IdentifierTypeNode($name));
	}

}
