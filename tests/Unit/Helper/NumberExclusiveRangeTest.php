<?php declare(strict_types = 1);

namespace Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Helper\NumberExclusiveRange;
use Shredio\TypeSchema\Helper\RangeExclusiveDecision;

final class NumberExclusiveRangeTest extends TestCase
{

	public function testDecideReturnsOkWhenValueIsInRange(): void
	{
		$range = NumberExclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(6));
		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(7));
		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(9));
	}

	public function testDecideReturnsOkWhenValueIsInRangeFloat(): void
	{
		$range = NumberExclusiveRange::fromFloats(min: 5.5, max: 10.5);

		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(5.6));
		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(7.8));
		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(10.4));
	}

	public function testDecideWithMinOnly(): void
	{
		$range = NumberExclusiveRange::fromInts(min: 5);

		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(6));
		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(10));
		$this->assertSame(RangeExclusiveDecision::ShouldBeGreater, $range->decide(5));
		$this->assertSame(RangeExclusiveDecision::ShouldBeGreater, $range->decide(4));
	}

	public function testDecideWithMinOnlyFloat(): void
	{
		$range = NumberExclusiveRange::fromFloats(min: 5.5);

		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(5.6));
		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(10.8));
		$this->assertSame(RangeExclusiveDecision::ShouldBeGreater, $range->decide(5.5));
		$this->assertSame(RangeExclusiveDecision::ShouldBeGreater, $range->decide(5.4));
	}

	public function testDecideWithMaxOnly(): void
	{
		$range = NumberExclusiveRange::fromInts(max: 10);

		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(5));
		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(9));
		$this->assertSame(RangeExclusiveDecision::ShouldBeLess, $range->decide(10));
		$this->assertSame(RangeExclusiveDecision::ShouldBeLess, $range->decide(11));
	}

	public function testDecideWithMaxOnlyFloat(): void
	{
		$range = NumberExclusiveRange::fromFloats(max: 10.5);

		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(5.2));
		$this->assertSame(RangeExclusiveDecision::Ok, $range->decide(10.4));
		$this->assertSame(RangeExclusiveDecision::ShouldBeLess, $range->decide(10.5));
		$this->assertSame(RangeExclusiveDecision::ShouldBeLess, $range->decide(10.6));
	}

	public function testDecideWithBothMinMaxValueEqualMin(): void
	{
		$range = NumberExclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame(RangeExclusiveDecision::ShouldBeGreater, $range->decide(5));
	}

	public function testDecideWithBothMinMaxValueEqualMax(): void
	{
		$range = NumberExclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame(RangeExclusiveDecision::ShouldBeLess, $range->decide(10));
	}

	public function testDecideWithBothMinMaxValueBelowMin(): void
	{
		$range = NumberExclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame(RangeExclusiveDecision::ShouldBeGreater, $range->decide(4));
	}

	public function testDecideWithBothMinMaxValueAboveMax(): void
	{
		$range = NumberExclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame(RangeExclusiveDecision::ShouldBeLess, $range->decide(11));
	}

	public function testDecideWithBothMinMaxFloatValueEqualMin(): void
	{
		$range = NumberExclusiveRange::fromFloats(min: 5.5, max: 10.5);

		$this->assertSame(RangeExclusiveDecision::ShouldBeGreater, $range->decide(5.5));
	}

	public function testDecideWithBothMinMaxFloatValueEqualMax(): void
	{
		$range = NumberExclusiveRange::fromFloats(min: 5.5, max: 10.5);

		$this->assertSame(RangeExclusiveDecision::ShouldBeLess, $range->decide(10.5));
	}

	public function testDecideWithBothMinMaxFloatValueBelowMin(): void
	{
		$range = NumberExclusiveRange::fromFloats(min: 5.5, max: 10.5);

		$this->assertSame(RangeExclusiveDecision::ShouldBeGreater, $range->decide(5.4));
	}

	public function testDecideWithBothMinMaxFloatValueAboveMax(): void
	{
		$range = NumberExclusiveRange::fromFloats(min: 5.5, max: 10.5);

		$this->assertSame(RangeExclusiveDecision::ShouldBeLess, $range->decide(10.6));
	}

	public function testDecideWithNoLimits(): void
	{
		$intRange = NumberExclusiveRange::fromInts();
		$floatRange = NumberExclusiveRange::fromFloats();

		$this->assertSame(RangeExclusiveDecision::Ok, $intRange->decide(0));
		$this->assertSame(RangeExclusiveDecision::Ok, $intRange->decide(-100));
		$this->assertSame(RangeExclusiveDecision::Ok, $intRange->decide(100));
		$this->assertSame(RangeExclusiveDecision::Ok, $floatRange->decide(0.0));
		$this->assertSame(RangeExclusiveDecision::Ok, $floatRange->decide(-100.5));
		$this->assertSame(RangeExclusiveDecision::Ok, $floatRange->decide(100.5));
	}

	public function testToStringWithParentheses(): void
	{
		$range = NumberExclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame('(5, 10)', $range->toString());
		$this->assertSame('(5, 10)', $range->toString(true));
	}

	public function testToStringWithoutParentheses(): void
	{
		$range = NumberExclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame('5, 10', $range->toString(false));
	}

	public function testToStringWithNoLimits(): void
	{
		$range = NumberExclusiveRange::fromInts();

		$this->assertSame('(min, max)', $range->toString());
		$this->assertSame('min, max', $range->toString(false));
	}

	public function testToStringWithMinOnly(): void
	{
		$range = NumberExclusiveRange::fromInts(min: 5);

		$this->assertSame('(5, max)', $range->toString());
		$this->assertSame('5, max', $range->toString(false));
	}

	public function testToStringWithMaxOnly(): void
	{
		$range = NumberExclusiveRange::fromInts(max: 10);

		$this->assertSame('(min, 10)', $range->toString());
		$this->assertSame('min, 10', $range->toString(false));
	}

	public function testFromFloatsThrowsExceptionForNanMin(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Min cannot be NaN.');

		NumberExclusiveRange::fromFloats(min: NAN);
	}

	public function testFromFloatsThrowsExceptionForNanMax(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Max cannot be NaN.');

		NumberExclusiveRange::fromFloats(max: NAN);
	}

	public function testFromFloatsThrowsExceptionForInfiniteMin(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Min cannot be infinite, use null instead.');

		NumberExclusiveRange::fromFloats(min: INF);
	}

	public function testFromFloatsThrowsExceptionForInfiniteMax(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Max cannot be infinite, use null instead.');

		NumberExclusiveRange::fromFloats(max: INF);
	}

}
