<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Helper;

use Nette\Utils\Floats;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

/**
 * @template TValue of int|float=int|float
 * @implements NumberRange<TValue>
 */
final readonly class NumberExclusiveRange implements NumberRange
{

	/**
	 * @param TValue|null $min
	 * @param TValue|null $max
	 */
	private function __construct(
		private int|float|null $min = null,
		private int|float|null $max = null,
		private bool $exact = false,
	)
	{
	}

	/**
	 * @return self<float>
	 */
	public static function fromFloats(
		float|null $min = null,
		float|null $max = null,
	): self
	{
		if ($min !== null) {
			if (is_nan($min)) {
				throw new \InvalidArgumentException('Min cannot be NaN.');
			}
			if (is_infinite($min)) {
				throw new \InvalidArgumentException('Min cannot be infinite, use null instead.');
			}
		}
		if ($max !== null) {
			if (is_nan($max)) {
				throw new \InvalidArgumentException('Max cannot be NaN.');
			}
			if (is_infinite($max)) {
				throw new \InvalidArgumentException('Max cannot be infinite, use null instead.');
			}
		}

		return new self($min, $max);
	}

	/**
	 * @return self<int>
	 */
	public static function fromInts(
		int|null $min = null,
		int|null $max = null,
	): self
	{
		return new self($min, $max);
	}

	/**
	 * @return self<int>
	 */
	public static function exactInt(int $value): self
	{
		return new self($value, $value, true);
	}

	/**
	 * @return self<float>
	 */
	public static function exactFloat(float $value): self
	{
		if (is_nan($value)) {
			throw new \InvalidArgumentException('Value cannot be NaN.');
		}
		if (is_infinite($value)) {
			throw new \InvalidArgumentException('Value cannot be infinite.');
		}

		return new self($value, $value, true);
	}

	public function getMax(): int|float|null
	{
		return $this->max;
	}

	public function getMin(): int|float|null
	{
		return $this->min;
	}

	public function getExactValue(): int|float|null
	{
		if ($this->exact) {
			return $this->min; // min and max are equal
		}

		return null;
	}

	/**
	 * @param TValue $value
	 */
	public function decide(int|float $value): RangeExclusiveDecision
	{
		if ($this->min !== null) {
			if (is_float($this->min)) {
				if (Floats::isLessThanOrEqualTo($value, $this->min)) {
					return RangeExclusiveDecision::ShouldBeGreater;
				}
			} else if ($value <= $this->min) {
				return RangeExclusiveDecision::ShouldBeGreater;
			}
		}

		if ($this->max !== null) {
			if (is_float($this->max)) {
				if (Floats::isGreaterThanOrEqualTo($value, $this->max)) {
					return RangeExclusiveDecision::ShouldBeLess;
				}
			} else if ($value >= $this->max) {
				return RangeExclusiveDecision::ShouldBeLess;
			}
		}

		return RangeExclusiveDecision::Ok;
	}

	public function toString(bool $parentheses = true): string
	{
		if ($this->min === null && $this->max === null) {
			if ($parentheses) {
				return '(min, max)';
			}

			return 'min, max';
		}

		if ($parentheses) {
			$minSymbol = '(';
			$maxSymbol = ')';
		} else {
			$minSymbol = $maxSymbol = '';
		}
		$min = $this->min === null ? 'min' : (string) $this->min;
		$max = $this->max === null ? 'max' : (string) $this->max;

		return sprintf('%s%s, %s%s', $minSymbol, $min, $max, $maxSymbol);
	}

	/**
	 * @return list{ TypeNode, TypeNode }
	 */
	public function getTypeGenericNodes(): array
	{
		$types = [];
		if ($this->min === null) {
			$types[] = new IdentifierTypeNode('min');
		} else {
			$types[] = new ConstTypeNode(new ConstExprIntegerNode((string) $this->min));
		}

		if ($this->max === null) {
			$types[] = new IdentifierTypeNode('max');
		} else {
			$types[] = new ConstTypeNode(new ConstExprIntegerNode((string) $this->max));
		}

		return $types;
	}

}
