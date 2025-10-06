<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\PhpStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
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
		$args = $methodCall->getArgs();
		if (!isset($args[0])) {
			return null;
		}

		$arg = $scope->getType($args[0]->value);
		$arrays = $arg->getConstantArrays();
		if (count($arrays) === 0) {
			return null;
		}

		$builder = ConstantArrayTypeBuilder::createEmpty();
		foreach ($arrays as $arrayType) {
			foreach ($arrayType->getKeyTypes() as $key) {
				$valueType = $arrayType->getOffsetValueType($key);
				[$type, $optional] = $this->extractOptional($valueType->getTemplateType(SchemaType::class, 'T'));

				$builder->setOffsetValueType($key, $type, $optional);
			}
		}

		return new GenericObjectType(SchemaType::class, [$builder->getArray()]);
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
