<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Issue\Path;
use Shredio\TypeSchema\Issue\Report\ErrorReport;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\TypeSchema;
use Tests\TestCase;

final class BeforeTypeTest extends TestCase
{

	private int $mappedIssues = 0;

	public function testBeforeString(): void
	{
		$s = TypeSchema::get();

		self::assertSame('string', $this->validStrictParse(
			$s->before(fn (): string => 'string', $s->string()),
			null,
		));
	}

	public function testBeforeArrayShape(): void
	{
		$s = TypeSchema::get();
		$type = $s->before(
			static fn (mixed $value): mixed => is_string($value) ? json_decode($value, true) : $value,
			$s->arrayShape(['id' => $s->int()]),
		);

		self::assertSame(['id' => 5], $this->validStrictParse($type, '{"id": 5}'));
	}

	public function testFailureIsReturnedAsIsWithoutIssueMapper(): void
	{
		$s = TypeSchema::get();

		$result = $this->getProcessor()->parse('x', $s->before(static fn (mixed $value): mixed => $value, $s->int()));

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertSame([null], $this->paths($result->getReports()));
	}

	public function testIssueMapperIsAppliedToErrors(): void
	{
		$s = TypeSchema::get();

		$result = $this->getProcessor()->parse('x', $s->before(static fn (mixed $value): mixed => $value, $s->int(), $this->wrapIssues(...)));

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertSame(['wrapped'], $this->paths($result->getReports()));
	}

	public function testIssueMapperIsAppliedToNoticesOfParsedValue(): void
	{
		$s = TypeSchema::get();
		$type = $s->before(static fn (mixed $value): mixed => $value, $s->arrayShape(['id' => $s->int()]), $this->wrapIssues(...));

		$result = $this->getProcessor()->parse(['id' => 1, 'extra' => 2], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['id' => 1], $result->value);
		$this->assertSame(['wrapped.extra'], $this->paths($result->getNoticeReports()));
	}

	public function testIssueMapperIsAppliedToErrorsAndNoticesOfFailure(): void
	{
		$s = TypeSchema::get();
		$type = $s->before(static fn (mixed $value): mixed => $value, $s->list($s->arrayShape(['id' => $s->int()])), $this->wrapIssues(...));

		$result = $this->getProcessor()->parse([['id' => 1, 'extra' => 2], ['id' => 'x']], $type);

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertSame(['wrapped.[1].id'], $this->paths($result->getReports()));
		$this->assertSame(['wrapped.[0].extra'], $this->paths($result->getNoticeReports()));
		$this->assertSame(2, $this->mappedIssues);
	}

	public function testIssueMapperIsNotCalledForCleanValue(): void
	{
		$s = TypeSchema::get();
		$type = $s->before(static fn (mixed $value): mixed => $value, $s->int(), $this->wrapIssues(...));

		$this->assertSame(5, $this->validStrictParse($type, 5));
		$this->assertSame(0, $this->mappedIssues);
	}

	private function wrapIssues(IssueNode $issues): IssueNode
	{
		$this->mappedIssues++;

		return new IssuePath($issues, new Path('wrapped'));
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
