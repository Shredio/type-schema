<?php declare(strict_types = 1);

namespace Tests\Unit\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Helper\FloatComparison;

final class FloatComparisonTest extends TestCase
{

	/**
	 * @return iterable<string, array{float, float, int}>
	 */
	public static function provideComparisons(): iterable
	{
		yield 'equal' => [1.5, 1.5, 0];
		yield 'rounding error' => [0.1 + 0.2, 0.3, 0];
		yield 'tiny absolute difference' => [0.0, 1e-11, 0];
		yield 'relative difference of large numbers' => [1e20, 1e20 + 1e9, 0];
		yield 'less' => [1.0, 1.0001, -1];
		yield 'greater' => [1.0001, 1.0, 1];
		yield 'negative' => [-2.0, -1.0, -1];
		yield 'same infinity' => [INF, INF, 0];
		yield 'same negative infinity' => [-INF, -INF, 0];
		yield 'infinity is greater than finite' => [INF, PHP_FLOAT_MAX, 1];
		yield 'negative infinity is less than infinity' => [-INF, INF, -1];
	}

	#[DataProvider('provideComparisons')]
	public function testCompare(float $a, float $b, int $expected): void
	{
		$this->assertSame($expected, FloatComparison::compare($a, $b));
		$this->assertSame(-$expected, FloatComparison::compare($b, $a));
		$this->assertSame($expected < 0, FloatComparison::isLessThan($a, $b));
		$this->assertSame($expected <= 0, FloatComparison::isLessThanOrEqualTo($a, $b));
		$this->assertSame($expected > 0, FloatComparison::isGreaterThan($a, $b));
		$this->assertSame($expected >= 0, FloatComparison::isGreaterThanOrEqualTo($a, $b));
	}

	public function testNanCannotBeCompared(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Trying to compare NAN.');

		FloatComparison::compare(NAN, 1.0);
	}

	public function testNanCannotBeComparedAsSecondArgument(): void
	{
		$this->expectException(LogicException::class);

		FloatComparison::compare(1.0, NAN);
	}

}
