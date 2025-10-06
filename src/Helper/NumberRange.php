<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Helper;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;

/**
 * @template TValue of int|float=int|float
 */
interface NumberRange
{

	/**
	 * @param TValue $value
	 */
	public function decide(int|float $value): RangeInclusiveDecision|RangeExclusiveDecision;

	/**
	 * @return TValue|null
	 */
	public function getMin(): int|float|null;

	/**
	 * @return TValue|null
	 */
	public function getMax(): int|float|null;

	/**
	 * @return TValue|null Exact value if min and max are equal, null otherwise
	 */
	public function getExactValue(): int|float|null;

	public function toString(bool $parentheses = true): string;

	/**
	 * @return list{ TypeNode, TypeNode }
	 */
	public function getTypeGenericNodes(): array;

}
