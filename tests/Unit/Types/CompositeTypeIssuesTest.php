<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Config\TypeHierarchyConfig;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Helper\RangeInclusiveDecision;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\ItemCountOutOfRange;
use Shredio\TypeSchema\Issue\Report\ErrorReport;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\TypeSchema;
use Tests\TestCase;

/**
 * Fail-fast parsing stops at the first error, collecting mode reports all of them.
 * Notices found before an error are kept in the failure.
 */
final class CompositeTypeIssuesTest extends TestCase
{

	/**
	 * @return iterable<string, array{Type<mixed>, mixed, list<string>, list<string>}>
	 */
	public static function provideValuesWithSeveralErrors(): iterable
	{
		$t = TypeSchema::get();

		yield 'array values' => [$t->array($t->string(), $t->int()), ['a' => 'x', 'b' => 1, 'c' => 'y'], ['a'], ['a', 'c']];
		yield 'array keys' => [$t->array($t->int(), $t->int()), ['a' => 1, 5 => 2, 'b' => 3], ['a'], ['a', 'b']];
		yield 'array key and value' => [$t->array($t->int(), $t->int()), ['a' => 1, 5 => 'x'], ['a'], ['a', '[5]']];
		yield 'list items' => [$t->list($t->int()), ['x', 1, 'y'], ['[0]'], ['[0]', '[2]']];
		yield 'non-empty list items' => [$t->nonEmptyList($t->int()), ['x', 1, 'y'], ['[0]'], ['[0]', '[2]']];
		yield 'shape missing keys' => [self::shape(), [], ['a'], ['a', 'b']];
		yield 'shape invalid and missing key' => [self::shape(), ['a' => 'x'], ['a'], ['a', 'b']];
		yield 'shape optional keys' => [
			$t->arrayShape(['a' => $t->optional($t->int()), 'b' => $t->optional($t->int())]),
			['a' => 'x', 'b' => 'y'],
			['a'],
			['a', 'b'],
		];
		yield 'shape rest keys' => [$t->arrayShape(['a' => $t->int()], rest: $t->int()), ['a' => 1, 'x' => 's', 'y' => 't'], ['x'], ['x', 'y']];
	}

	/**
	 * @param Type<mixed> $type
	 * @param list<string> $failFastPaths
	 * @param list<string> $collectedPaths
	 */
	#[DataProvider('provideValuesWithSeveralErrors')]
	public function testFailFastStopsAtFirstErrorAndCollectingReportsAll(
		Type $type,
		mixed $value,
		array $failFastPaths,
		array $collectedPaths,
	): void
	{
		$this->assertSame($failFastPaths, $this->paths($this->parseFailure($type, $value)->getReports()));
		$this->assertSame($collectedPaths, $this->paths($this->parseFailure($type, $value, collectErrors: true)->getReports()));
	}

	/**
	 * @return iterable<string, array{Type<mixed>, mixed, list<string>, list<string>}>
	 */
	public static function provideNoticesFollowedByError(): iterable
	{
		$t = TypeSchema::get();
		$item = $t->arrayShape(['id' => $t->int()]);
		$withExtra = ['id' => 1, 'extra' => true];

		yield 'array' => [$t->array($t->string(), $item), ['a' => $withExtra, 'b' => ['id' => 'x']], ['b.id'], ['a.extra']];
		yield 'array key after notice' => [$t->array($t->string(), $item), ['a' => $withExtra, 5 => ['id' => 1]], ['[5]'], ['a.extra']];
		yield 'list' => [$t->list($item), [$withExtra, ['id' => 'x']], ['[1].id'], ['[0].extra']];
		yield 'non-empty list' => [$t->nonEmptyList($item), [$withExtra, ['id' => 'x']], ['[1].id'], ['[0].extra']];
		yield 'shape required key' => [$t->arrayShape(['a' => $item, 'b' => $t->int()]), ['a' => $withExtra, 'b' => 'x'], ['b'], ['a.extra']];
		yield 'shape missing key' => [$t->arrayShape(['a' => $item, 'b' => $t->int()]), ['a' => $withExtra], ['b'], ['a.extra']];
		yield 'shape optional key' => [
			$t->arrayShape(['a' => $t->int(), 'b' => $t->optional($t->int())]),
			['a' => 1, 'x' => 1, 'b' => 'y'],
			['b'],
			['x'],
		];
		yield 'shape optional key after nested notice' => [
			$t->arrayShape(['a' => $t->optional($item), 'b' => $t->optional($t->int())]),
			['a' => $withExtra, 'b' => 'y'],
			['b'],
			['a.extra'],
		];
	}

	/**
	 * @param Type<mixed> $type
	 * @param list<string> $errorPaths
	 * @param list<string> $noticePaths
	 */
	#[DataProvider('provideNoticesFollowedByError')]
	public function testFailureKeepsNoticesFoundBeforeError(Type $type, mixed $value, array $errorPaths, array $noticePaths): void
	{
		$failure = $this->parseFailure($type, $value);

		$this->assertSame($errorPaths, $this->paths($failure->getReports()));
		$this->assertSame($noticePaths, $this->paths($failure->getNoticeReports()));
	}

	/**
	 * @return iterable<string, array{Type<mixed>, mixed, list<string>, list<string>}>
	 */
	public static function provideErrorsFollowedByNotices(): iterable
	{
		$t = TypeSchema::get();
		$item = $t->arrayShape(['id' => $t->int()]);
		$withExtra = ['id' => 1, 'extra' => true];

		yield 'array' => [$t->array($t->string(), $item), ['a' => ['id' => 'x'], 'b' => $withExtra], ['a.id'], ['b.extra']];
		yield 'list' => [$t->list($item), [['id' => 'x'], $withExtra], ['[0].id'], ['[1].extra']];
		yield 'non-empty list' => [$t->nonEmptyList($item), [['id' => 'x'], $withExtra], ['[0].id'], ['[1].extra']];
		yield 'shape' => [$t->arrayShape(['a' => $t->int(), 'b' => $item]), ['a' => 'x', 'b' => $withExtra], ['a'], ['b.extra']];
		yield 'shape extra key' => [$t->arrayShape(['a' => $t->int()]), ['a' => 'x', 'extra' => 1], ['a'], ['extra']];
		yield 'shape optional key' => [$t->arrayShape(['a' => $t->int(), 'opt' => $t->optional($item)]), ['a' => 'x', 'opt' => $withExtra], ['a'], ['opt.extra']];
	}

	/**
	 * @param Type<mixed> $type
	 * @param list<string> $errorPaths
	 * @param list<string> $noticePaths
	 */
	#[DataProvider('provideErrorsFollowedByNotices')]
	public function testCollectingKeepsNoticesFoundAfterError(Type $type, mixed $value, array $errorPaths, array $noticePaths): void
	{
		$failure = $this->parseFailure($type, $value, collectErrors: true);

		$this->assertSame($errorPaths, $this->paths($failure->getReports()));
		$this->assertSame($noticePaths, $this->paths($failure->getNoticeReports()));
	}

	public function testAllNoticesAreReported(): void
	{
		$t = TypeSchema::get();
		$item = $t->arrayShape(['id' => $t->int()]);

		$result = $this->getProcessor()->parse(
			['a' => ['id' => 1, 'x' => 1], 'b' => ['id' => 2, 'y' => 2]],
			$t->array($t->string(), $item),
		);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['a' => ['id' => 1], 'b' => ['id' => 2]], $result->value);
		$this->assertSame(['a.x', 'b.y'], $this->paths($result->getNoticeReports()));
	}

	public function testShapeReportsEveryExtraKeyAndParsesFollowingOptionalKeys(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape(['a' => $t->int(), 'opt' => $t->optional($t->int())]);

		$result = $this->getProcessor()->parse(['a' => 1, 'x' => 1, 'y' => 2, 'opt' => 3], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['a' => 1, 'opt' => 3], $result->value);
		$this->assertSame(['x', 'y'], $this->paths($result->getNoticeReports()));
	}

	public function testShapeUsesNestedConfigForOptionalAndRestKeys(): void
	{
		$t = TypeSchema::get();
		$lenient = new TypeConfig(ConversionStrategyFactory::lenient());
		$config = new TypeConfig(hierarchyConfig: TypeHierarchyConfig::fromArray(['opt' => $lenient, 'rest' => $lenient]));
		$type = $t->arrayShape(['a' => $t->int(), 'opt' => $t->optional($t->int())], rest: $t->int());

		$result = $this->getProcessor()->parse(['a' => 1, 'opt' => '2', 'rest' => '3'], $type, $config);
		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['a' => 1, 'opt' => 2, 'rest' => 3], $result->value);

		$this->assertSame(['a'], $this->paths($this->parseFailure($type, ['a' => '1', 'opt' => '2'], config: $config)->getReports()));
	}

	/**
	 * @return iterable<string, array{Type<mixed>, Generator<mixed>, mixed}>
	 */
	public static function provideGeneratorsWithKeys(): iterable
	{
		$t = TypeSchema::get();

		yield 'array' => [$t->array($t->string(), $t->int()), self::keyedGenerator(['a' => 1, 'b' => 2]), ['a' => 1, 'b' => 2]];
		yield 'shape' => [$t->arrayShape(['a' => $t->int(), 'b' => $t->int()]), self::keyedGenerator(['a' => 1, 'b' => 2]), ['a' => 1, 'b' => 2]];
		yield 'list' => [$t->list($t->int()), self::keyedGenerator([1, 2]), [1, 2]];
		yield 'non-empty list' => [$t->nonEmptyList($t->int()), self::keyedGenerator([1, 2]), [1, 2]];
	}

	/**
	 * @param Type<mixed> $type
	 * @param Generator<mixed> $generator
	 */
	#[DataProvider('provideGeneratorsWithKeys')]
	public function testGeneratorKeysArePreserved(Type $type, Generator $generator, mixed $expected): void
	{
		$this->assertSame($expected, $this->validStrictParse($type, $generator));
	}

	/**
	 * @return iterable<string, array{Type<mixed>}>
	 */
	public static function provideListTypes(): iterable
	{
		$t = TypeSchema::get();

		yield 'list' => [$t->list($t->int())];
		yield 'non-empty list' => [$t->nonEmptyList($t->int())];
	}

	/**
	 * @param Type<mixed> $type
	 */
	#[DataProvider('provideListTypes')]
	public function testGeneratorWithNonSequentialKeysIsNotList(Type $type): void
	{
		$failure = $this->parseFailure($type, self::keyedGenerator([5 => 1, 6 => 2]));

		$this->assertInstanceOf(InvalidType::class, $failure->getReports()[0]->issue);
	}

	public function testExpectedTypeDescribesCompositeTypes(): void
	{
		$t = TypeSchema::get();
		$type = $t->union([
			$t->arrayShape(['a' => $t->int(), 'b' => $t->optional($t->string()), 0 => $t->int(), 'foo-bar' => $t->int(), '1a' => $t->int(), 'a1' => $t->int()]),
			$t->arrayShape(['a' => $t->int()], rest: $t->string()),
			$t->array($t->string(), $t->int()),
			$t->list($t->int()),
			$t->nonEmptyList($t->string()),
		]);

		$this->assertSame(
			"Invalid type string with value \"x\", expected (array{a: int, 0: int, 'foo-bar': int, '1a': int, a1: int, b?: string} | array{a: int, ...<string>} | array<string, int> | list<int> | non-empty-list<string>).",
			$this->parseFailure($type, 'x')->getReports()[0]->messageForDeveloper,
		);
	}

	public function testEmptyNonEmptyListReportsItemCount(): void
	{
		$failure = $this->parseFailure(TypeSchema::get()->nonEmptyList(TypeSchema::get()->int()), []);

		$report = $failure->getReports()[0];
		$this->assertInstanceOf(ItemCountOutOfRange::class, $report->issue);
		$this->assertSame(0, $report->issue->count);
		$this->assertSame(1, $report->issue->range->getMin());
		$this->assertNull($report->issue->range->getMax());
		$this->assertNull($report->issue->range->getExactValue());
		$this->assertSame(RangeInclusiveDecision::ShouldBeGreaterOrEqual, $report->issue->decision);
		$this->assertSame('Item count 0 is not in the expected range [1, max].', $report->messageForDeveloper);
	}

	/**
	 * @return Type<mixed>
	 */
	private static function shape(): Type
	{
		$t = TypeSchema::get();

		return $t->arrayShape(['a' => $t->int(), 'b' => $t->int()]);
	}

	/**
	 * @param array<mixed> $values
	 * @return Generator<mixed>
	 */
	private static function keyedGenerator(array $values): Generator
	{
		yield from $values;
	}

	/**
	 * @param Type<mixed> $type
	 */
	private function parseFailure(Type $type, mixed $value, bool $collectErrors = false, ?TypeConfig $config = null): Failure
	{
		$result = $this->getProcessor()->parse($value, $type, $config, $collectErrors);
		$this->assertInstanceOf(Failure::class, $result);

		return $result;
	}

	/**
	 * @param list<ErrorReport> $reports
	 * @return list<string|null>
	 */
	private function paths(array $reports): array
	{
		return array_map(static fn (ErrorReport $report): ?string => $report->toDebugPathString(), $reports);
	}

}
