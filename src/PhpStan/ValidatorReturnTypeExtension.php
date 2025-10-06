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

	/** @var array<non-empty-string, array{ args: array<string>, create: callable }> */
	private array $mapping;

	public function __construct()
	{
		$this->mapping = [
			'intRange' => [
				'args' => ['int', 'int'],
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
		foreach ($mapping['args'] as $i => $requiredType) {
			if (!isset($args[$i])) {
				$values[] = null;
				continue;
			}
			$argType = $scope->getType($args[$i]->value);
			$constantScalars = $argType->getConstantScalarValues();
			if (count($constantScalars) !== 1) {
				$values[] = null;
				continue;
			}

			if (get_debug_type($constantScalars[0]) !== $requiredType) {
				$values[] = null;
				continue;
			}

			$valid = true;
			$values[] = $constantScalars[0];
		}

		if (!$valid) {
			return null;
		}

		return new GenericObjectType(AbstractType::class, [$mapping['create'](...$values)]); // @phpstan-ignore argument.type
	}

}
