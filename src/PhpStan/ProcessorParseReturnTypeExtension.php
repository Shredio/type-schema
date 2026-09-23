<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\PhpStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\Types\Type as SchemaType;
use Shredio\TypeSchema\TypeSchemaProcessor;

/**
 * Builds Success<T>|Failure from the Type<T> argument. PHPStan generalizes an inferred constant array inside
 * a generic return type (non-empty-string becomes string), so the plain template signature is not precise enough.
 */
final readonly class ProcessorParseReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return TypeSchemaProcessor::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'parse';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope,
	): ?Type
	{
		$typeArg = ArgumentFinder::find($methodCall->getArgs(), 1, 'type');
		if ($typeArg === null) {
			return null;
		}

		$valueType = $scope->getType($typeArg->value)->getTemplateType(SchemaType::class, 'T');
		if ($valueType instanceof ErrorType) {
			return null;
		}

		return TypeCombinator::union(
			new GenericObjectType(Success::class, [$valueType]),
			new ObjectType(Failure::class),
		);
	}

}
