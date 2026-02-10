<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Error\ErrorElement;

/**
 * @template-covariant T
 * @extends Type<T>
 */
final readonly class ValidateType extends Type
{

	/** @var callable(T $value, TypeContext $context): ?ErrorElement */
	private mixed $callback;

	/**
	 * @param Type<T> $type
	 * @param callable(T $value, TypeContext $context): ?ErrorElement $callback
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

		return ($this->callback)($val, $context) ?? $val;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return $this->type->getTypeNode($context);
	}

}
