<?php declare(strict_types = 1);

namespace Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;

final class NumberInclusiveRangeTest extends TestCase
{

	public function testDecideReturnsOkWhenValueIsInRange(): void
	{
		$range = NumberInclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(5));
		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(7));
		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(10));
	}

	public function testDecideReturnsOkWhenValueIsInRangeFloat(): void
	{
		$range = NumberInclusiveRange::fromFloats(min: 5.5, max: 10.5);

		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(5.5));
		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(7.8));
		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(10.5));
	}

	public function testDecideWithMinOnly(): void
	{
		$range = NumberInclusiveRange::fromInts(min: 5);

		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(5));
		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(10));
		$this->assertSame(RangeInclusiveDecision::ShouldBeGreaterOrEqual, $range->decide(4));
	}

	public function testDecideWithMinOnlyFloat(): void
	{
		$range = NumberInclusiveRange::fromFloats(min: 5.5);

		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(5.5));
		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(10.8));
		$this->assertSame(RangeInclusiveDecision::ShouldBeGreaterOrEqual, $range->decide(5.4));
	}

	public function testDecideWithMaxOnly(): void
	{
		$range = NumberInclusiveRange::fromInts(max: 10);

		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(5));
		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(10));
		$this->assertSame(RangeInclusiveDecision::ShouldBeLessOrEqual, $range->decide(11));
	}

	public function testDecideWithMaxOnlyFloat(): void
	{
		$range = NumberInclusiveRange::fromFloats(max: 10.5);

		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(5.2));
		$this->assertSame(RangeInclusiveDecision::Ok, $range->decide(10.5));
		$this->assertSame(RangeInclusiveDecision::ShouldBeLessOrEqual, $range->decide(10.6));
	}

	public function testDecideWithBothMinMaxValueBelowMin(): void
	{
		$range = NumberInclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame(RangeInclusiveDecision::ShouldBeGreaterOrEqual, $range->decide(4));
	}

	public function testDecideWithBothMinMaxValueAboveMax(): void
	{
		$range = NumberInclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame(RangeInclusiveDecision::ShouldBeLessOrEqual, $range->decide(11));
	}

	public function testDecideWithBothMinMaxFloatValueBelowMin(): void
	{
		$range = NumberInclusiveRange::fromFloats(min: 5.5, max: 10.5);

		$this->assertSame(RangeInclusiveDecision::ShouldBeGreaterOrEqual, $range->decide(5.4));
	}

	public function testDecideWithBothMinMaxFloatValueAboveMax(): void
	{
		$range = NumberInclusiveRange::fromFloats(min: 5.5, max: 10.5);

		$this->assertSame(RangeInclusiveDecision::ShouldBeLessOrEqual, $range->decide(10.6));
	}

	public function testDecideWithNoLimits(): void
	{
		$intRange = NumberInclusiveRange::fromInts();
		$floatRange = NumberInclusiveRange::fromFloats();

		$this->assertSame(RangeInclusiveDecision::Ok, $intRange->decide(0));
		$this->assertSame(RangeInclusiveDecision::Ok, $intRange->decide(-100));
		$this->assertSame(RangeInclusiveDecision::Ok, $intRange->decide(100));
		$this->assertSame(RangeInclusiveDecision::Ok, $floatRange->decide(0.0));
		$this->assertSame(RangeInclusiveDecision::Ok, $floatRange->decide(-100.5));
		$this->assertSame(RangeInclusiveDecision::Ok, $floatRange->decide(100.5));
	}

	public function testToStringWithParentheses(): void
	{
		$range = NumberInclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame('[5, 10]', $range->toString());
		$this->assertSame('[5, 10]', $range->toString(true));
	}

	public function testToStringWithoutParentheses(): void
	{
		$range = NumberInclusiveRange::fromInts(min: 5, max: 10);

		$this->assertSame('5, 10', $range->toString(false));
	}

	public function testToStringWithNoLimits(): void
	{
		$range = NumberInclusiveRange::fromInts();

		$this->assertSame('[min, max]', $range->toString());
		$this->assertSame('min, max', $range->toString(false));
	}

	public function testToStringWithMinOnly(): void
	{
		$range = NumberInclusiveRange::fromInts(min: 5);

		$this->assertSame('[5, max]', $range->toString());
		$this->assertSame('5, max', $range->toString(false));
	}

	public function testToStringWithMaxOnly(): void
	{
		$range = NumberInclusiveRange::fromInts(max: 10);

		$this->assertSame('[min, 10]', $range->toString());
		$this->assertSame('min, 10', $range->toString(false));
	}

	public function testFromFloatsThrowsExceptionForNanMin(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Min cannot be NaN.');

		NumberInclusiveRange::fromFloats(min: NAN);
	}

	public function testFromFloatsThrowsExceptionForNanMax(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Max cannot be NaN.');

		NumberInclusiveRange::fromFloats(max: NAN);
	}

	public function testFromFloatsThrowsExceptionForInfiniteMin(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Min cannot be infinite, use null instead.');

		NumberInclusiveRange::fromFloats(min: INF);
	}

	public function testFromFloatsThrowsExceptionForInfiniteMax(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Max cannot be infinite, use null instead.');

		NumberInclusiveRange::fromFloats(max: INF);
	}

}
