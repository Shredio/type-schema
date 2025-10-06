<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\TypeSystem\TypeNodeHelper;

/**
 * @template-covariant T
 * @template U
 * @extends Type<U>
 */
final readonly class AfterType extends Type
{

	/** @var callable(T): U */
	private mixed $callback;

	/**
	 * @param Type<T> $type
	 * @param callable(T): U $callback
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
		if ($this->isError($val)) {
			return $val;
		}

		return ($this->callback)($val);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return TypeNodeHelper::fromReflection(new \ReflectionFunction(($this->callback)(...))) ?? new IdentifierTypeNode('mixed');
	}

}
