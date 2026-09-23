<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\PhpStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\Type;
use Shredio\TypeSchema\Types\OptionalType;
use Shredio\TypeSchema\Types\Type as AbstractType;
use Shredio\TypeSchema\TypeSchema;

final readonly class ValidatorReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var array<non-empty-string, array{ intArgumentCount: int<1, max>, create: callable(int|null, int|null): Type }> */
	private array $mapping;

	public function __construct()
	{
		$this->mapping = [
			'intRange' => [
				'intArgumentCount' => 2,
				'create' => static fn (?int $min, ?int $max): Type => IntegerRangeType::fromInterval($min, $max),
			],
		];
	}

	public function getClass(): string
	{
		return TypeSchema::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		$methodName = $methodReflection->getName();
		if ($methodName === 'optional') {
			return true;
		}

		return isset($this->mapping[$methodName]);
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope,
	): ?Type
	{
		$methodName = $methodReflection->getName();
		if ($methodName === 'optional') {
			$args = $methodCall->getArgs();
			if (!isset($args[0])) {
				return null;
			}

			$argType = $scope->getType($args[0]->value);
			$templateType = $argType->getTemplateType(AbstractType::class, 'T');
			if ($templateType instanceof ErrorType) {
				return null;
			}

			return new GenericObjectType(AbstractType::class, [
				new GenericObjectType(OptionalType::class, [$templateType])
			]);
		}
		$mapping = $this->mapping[$methodName] ?? null;
		if (!$mapping) {
			return null;
		}

		$args = $methodCall->getArgs();
		if (!isset($args[0])) {
			return null;
		}

		$valid = false;
		$values = [];
		for ($i = 0; $i < $mapping['intArgumentCount']; $i++) {
			$value = null;
			if (isset($args[$i])) {
				$constantScalars = $scope->getType($args[$i]->value)->getConstantScalarValues();
				if (count($constantScalars) === 1 && is_int($constantScalars[0])) {
					$value = $constantScalars[0];
					$valid = true;
				}
			}

			$values[] = $value;
		}

		if (!$valid) {
			return null;
		}

		return new GenericObjectType(AbstractType::class, [$mapping['create'](...$values)]);
	}

}
