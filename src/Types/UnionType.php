<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Types;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Issue\ErrorCategory;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\WithNotices;

/**
 * @extends Type<mixed>
 */
final readonly class UnionType extends Type
{

	/**
	 * @param non-empty-list<Type<mixed>> $types
	 */
	public function __construct(
		private array $types,
	)
	{
	}

	/**
	 * The first member parsed without notices wins. A member parsed with notices (e.g. extra keys) is used only when
	 * no member matches cleanly. When all members fail, the failure of the first member that failed only on
	 * validation constraints is returned, because its type matched; otherwise the whole union is an invalid type.
	 */
	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		$firstWithNotices = null;
		$firstValidationFailure = null;
		foreach ($this->types as $type) {
			$val = $type->parse($valueToParse, $context);
			if ($val instanceof Failure) {
				if ($firstValidationFailure === null && $val->getCategory() === ErrorCategory::Validation) {
					$firstValidationFailure = $val;
				}

				continue;
			}

			if ($val instanceof WithNotices) {
				$firstWithNotices ??= $val;

				continue;
			}

			return $val;
		}

		return $firstWithNotices ?? $firstValidationFailure ?? $this->createInvalidTypeFailure($valueToParse, $context);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new UnionTypeNode(array_map(fn (Type $type): TypeNode => $type->getTypeNode($context), $this->types));
	}

}
