<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Validation\TypeSystem\TypeNodeHelper;

/**
 * @template-covariant T
 * @template U
 * @extends Type<U>
 */
final readonly class AfterType extends Type
{

	/** @var callable(T $value, TypeContext $context): U */
	private mixed $callback;

	/**
	 * @param Type<T> $type
	 * @param callable(T $value, TypeContext $context): U $callback
	 */
	public function __construct(
		private Type $type,
		callable $callback,
	)
	{
		$this->callback = $callback;
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		$val = $this->type->parse($valueToParse, $context);
		if ($val instanceof Failure) {
			return $val;
		}

		if ($this->hasNotices($val)) {
			return $val->withValue(($this->callback)($val->value, $context));
		}

		return ($this->callback)($val, $context);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return TypeNodeHelper::fromReflection(new \ReflectionFunction(($this->callback)(...))) ?? new IdentifierTypeNode('mixed');
	}

}
