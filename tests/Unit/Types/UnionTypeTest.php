<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Issue\ErrorCategory;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\NumberOutOfRange;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\Types\BoolType;
use Shredio\TypeSchema\Types\IntType;
use Shredio\TypeSchema\Types\StringType;
use Shredio\TypeSchema\Types\UnionType;
use Shredio\TypeSchema\TypeSchema;
use Tests\TypeTestCase;

final class UnionTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		yield $this->typeToTest(new UnionType([new StringType(), new IntType()]), ['string']);
		yield 'string' => 'hello';
		yield 'empty string' => '';
		yield 'numeric string' => '123';

		yield $this->typeToTest(new UnionType([new StringType(), new IntType()]), ['string', 'int']);
		yield 'integer' => 123;
		yield 'zero' => 0;
		yield 'negative integer' => -123;

		yield $this->typeToTest(new UnionType([new StringType(), new BoolType()]), ['string']);
		yield 'string for bool union' => 'test';

		yield $this->typeToTest(new UnionType([new StringType(), new BoolType()]), ['string', 'bool']);
		yield 'boolean true' => true;
		yield 'boolean false' => false;
	}

	protected function getInvalidValues(): iterable
	{
		yield $this->typeToTest(new UnionType([new StringType(), new IntType()]));
		yield 'float' => 45.67;
		yield 'NAN value' => NAN;
		yield 'INF value' => INF;
		yield 'negative INF value' => -INF;
		yield 'null value' => null;
		yield 'empty array' => [];
		yield 'stdClass object' => new \stdClass();
		yield 'stringable object' => $this->stringable('hello');
		yield 'callable object' => $this->callableObject('hello');
		yield 'closure' => function () {};
		yield 'anonymous class' => new class {};
		yield 'resource' => $this->resource();
		yield 'generator' => $this->generator(['hello']);
		yield 'array iterator' => new \ArrayIterator(['hello']);
	}

	public function testSingleTypeReturnsDirectly(): void
	{
		$stringType = new StringType();
		$unionType = new UnionType([$stringType]);

		$this->assertTrue($this->checkType($unionType, 'hello'));
		$this->assertFalse($this->checkType($unionType, 123));
	}

	public function testMultipleTypesWithPriority(): void
	{
		$unionType = new UnionType([new StringType(), new IntType()]);

		$this->assertTrue($this->checkType($unionType, 'hello'));
		$this->assertTrue($this->checkType($unionType, 123));
		$this->assertFalse($this->checkType($unionType, 45.67));
		$this->assertFalse($this->checkType($unionType, true));
	}

	public function testPrefersMemberWithoutNotices(): void
	{
		$t = TypeSchema::get();
		$type = $t->union([
			$t->arrayShape(['a' => $t->int()]),
			$t->arrayShape(['a' => $t->int(), 'b' => $t->int()]),
		]);

		$result = $this->getProcessor()->parse(['a' => 1, 'b' => 2], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['a' => 1, 'b' => 2], $result->value);
		$this->assertFalse($result->hasNotices());
	}

	public function testFallsBackToFirstMemberWithNotices(): void
	{
		$t = TypeSchema::get();
		$type = $t->union([
			$t->arrayShape(['a' => $t->int()]),
			$t->arrayShape(['a' => $t->int(), 'b' => $t->int()]),
		]);

		$result = $this->getProcessor()->parse(['a' => 1, 'c' => 3], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['a' => 1], $result->value);
		$this->assertSame([['c']], array_map(static fn ($report): array => $report->toArrayPath(), $result->getNoticeReports()));
	}

	public function testNoticesOfFailedMembersAreDiscarded(): void
	{
		$t = TypeSchema::get();
		$type = $t->union([
			$t->arrayShape(['a' => $t->string()]),
			$t->arrayShape(['a' => $t->int(), 'b' => $t->int()]),
		]);

		$result = $this->getProcessor()->parse(['a' => 1, 'b' => 2], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertFalse($result->hasNotices());
	}

	public function testReturnsValidationFailureOfMatchingMember(): void
	{
		$t = TypeSchema::get();
		$type = $t->union([$t->intRange(1, 10), $t->string()]);

		$result = $this->getProcessor()->parse(20, $type);

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertSame(ErrorCategory::Validation, $result->getCategory());
		$this->assertInstanceOf(NumberOutOfRange::class, $result->getReports()[0]->issue);
	}

	public function testReturnsInvalidTypeWhenNoMemberMatches(): void
	{
		$t = TypeSchema::get();
		$type = $t->union([$t->intRange(1, 10), $t->string()]);

		$result = $this->getProcessor()->parse(true, $type);

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertSame(ErrorCategory::Structural, $result->getCategory());
		$issue = $result->getReports()[0]->issue;
		$this->assertInstanceOf(InvalidType::class, $issue);
		$this->assertSame('(int<1, 10> | string)', $issue->definition->getStringType());
	}

	private function checkType(UnionType $type, mixed $value): bool
	{
		return $this->getProcessor()->matches($value, $type);
	}

}
