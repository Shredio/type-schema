<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\PhpStan;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;

final readonly class KeepReturnTypeExtension implements DynamicMethodReturnTypeExtension, DynamicStaticMethodReturnTypeExtension
{

	/**
	 * @param class-string $className
	 * @param array<string, int> $methods methodName => argPosition
	 */
	public function __construct(
		private string $className,
		private array $methods,
	)
	{
	}

	public function getClass(): string
	{
		return $this->className;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return isset($this->methods[$methodReflection->getName()]);
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope,
	): ?Type
	{
		$args = $methodCall->getArgs();
		$position = $this->methods[$methodReflection->getName()];
		$arg = $args[$position] ?? null;
		if ($arg === null) {
			return null;
		}

		return $scope->getType($arg->value);
	}

	public function isStaticMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getDeclaringClass()->getName() === $this->className
			   && isset($this->methods[$methodReflection->getName()]);
	}

	public function getTypeFromStaticMethodCall(
		MethodReflection $methodReflection,
		StaticCall $methodCall,
		Scope $scope,
	): ?Type
	{
		$args = $methodCall->getArgs();
		$position = $this->methods[$methodReflection->getName()];
		$arg = $args[$position] ?? null;
		if ($arg === null) {
			return null;
		}

		return $scope->getType($arg->value);
	}

}
