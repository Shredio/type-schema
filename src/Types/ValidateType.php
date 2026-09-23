<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\WithNotices;

/**
 * @template-covariant T
 * @extends Type<T>
 */
final readonly class ValidateType extends Type
{

	/** @var callable(T $value, TypeContext $context): ?IssueNode */
	private mixed $callback;

	/**
	 * @param Type<T> $type
	 * @param callable(T $value, TypeContext $context): ?IssueNode $callback
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

		if ($val instanceof WithNotices) {
			/** @var T $innerValue */
			$innerValue = $val->value;
			$errors = ($this->callback)($innerValue, $context);

			return $errors === null ? $val : new Failure($errors, $val->notices);
		}

		$errors = ($this->callback)($val, $context);

		return $errors === null ? $val : new Failure($errors);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return $this->type->getTypeNode($context);
	}

}
