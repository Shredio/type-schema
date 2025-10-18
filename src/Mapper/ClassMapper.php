<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
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

	/**
	 * @param class-string<T> $className
	 * @return TypeDefinition
	 */
	final protected function createDefinition(string $className): TypeDefinition
	{
		return new TypeDefinition(fn (): IdentifierTypeNode => new IdentifierTypeNode($className));
	}

}
