<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\PhpStan;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Shredio\TypeSchema\Types\OptionalType;
use Shredio\TypeSchema\Types\Type as SchemaType;
use Shredio\TypeSchema\TypeSchema;

final readonly class ArrayShapeReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return TypeSchema::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'arrayShape';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope,
	): ?Type
	{
		$schemaArg = ArgumentFinder::find($methodCall->getArgs(), 0, 'schema');
		if ($schemaArg === null) {
			return null;
		}

		$arrays = $scope->getType($schemaArg->value)->getConstantArrays();
		if (count($arrays) === 0) {
			return null;
		}

		$restType = $this->resolveRestType(ArgumentFinder::find($methodCall->getArgs(), 1, 'rest'), $scope);
		if ($restType === null) {
			return new GenericObjectType(SchemaType::class, [$this->createSealedShape($arrays)]);
		}

		return new GenericObjectType(SchemaType::class, [$this->createUnsealedShape($arrays, $restType)]);
	}

	/**
	 * @param list<ConstantArrayType> $arrays
	 */
	private function createSealedShape(array $arrays): Type
	{
		$builder = ConstantArrayTypeBuilder::createEmpty();
		foreach ($arrays as $arrayType) {
			foreach ($arrayType->getKeyTypes() as $key) {
				[$type, $optional] = $this->extractOptional($arrayType->getOffsetValueType($key)->getTemplateType(SchemaType::class, 'T'));

				$builder->setOffsetValueType($key, $type, $optional);
			}
		}

		return $builder->getArray();
	}

	/**
	 * PHPStan ignores the unsealed part of array shapes, so an open shape is described as a general array
	 * with known offsets: non-empty-array<array-key, V>&hasOffsetValue('key', V1)&...
	 *
	 * @param list<ConstantArrayType> $arrays
	 */
	private function createUnsealedShape(array $arrays, Type $restType): Type
	{
		$valueTypes = [$restType];
		$requiredOffsets = [];
		foreach ($arrays as $arrayType) {
			foreach ($arrayType->getKeyTypes() as $key) {
				[$type, $optional] = $this->extractOptional($arrayType->getOffsetValueType($key)->getTemplateType(SchemaType::class, 'T'));

				$valueTypes[] = $type;
				if (!$optional) {
					$requiredOffsets[] = [$key, $type];
				}
			}
		}

		$shape = new ArrayType(TypeCombinator::union(new IntegerType(), new StringType()), TypeCombinator::union(...$valueTypes));
		foreach ($requiredOffsets as [$key, $type]) {
			$shape = $shape->setOffsetValueType($key, $type);
		}

		return $shape;
	}

	private function resolveRestType(?Arg $restArg, Scope $scope): ?Type
	{
		if ($restArg === null) {
			return null;
		}

		$restType = $scope->getType($restArg->value)->getTemplateType(SchemaType::class, 'T');
		if ($restType instanceof ErrorType) {
			return null;
		}

		return $restType;
	}

	/**
	 * @return array{Type, bool}
	 */
	private function extractOptional(Type $type): array
	{
		$optionalType = $type->getTemplateType(OptionalType::class, 'T');
		if ($optionalType instanceof ErrorType) {
			return [$type, false];
		}

		return [$optionalType, true];
	}

}
