<?php declare(strict_types = 1);

namespace Tests\Unit;

use Shredio\TypeSchema\Exception\AssertException;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use Tests\TestCase;

final class NoticesTest extends TestCase
{

	public function testParseReturnsValueWithNotices(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape(['name' => $t->string()]);

		$result = $this->getProcessor()->parse(['name' => 'John', 'extra' => 1], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['name' => 'John'], $result->value);
		$this->assertTrue($result->hasNotices());
	}

	public function testNoticesHaveFullPathInNestedStructures(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape([
			'items' => $t->list($t->arrayShape([
				'address' => $t->arrayShape(['city' => $t->string()]),
			])),
			'tags' => $t->array($t->string(), $t->arrayShape(['label' => $t->string()])),
		]);

		$result = $this->getProcessor()->parse([
			'items' => [
				['address' => ['city' => 'Prague']],
				['address' => ['city' => 'Brno', 'zip' => '60200']],
			],
			'tags' => [
				'php' => ['label' => 'PHP', 'color' => 'blue'],
			],
		], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame([
			'items' => [
				['address' => ['city' => 'Prague']],
				['address' => ['city' => 'Brno']],
			],
			'tags' => [
				'php' => ['label' => 'PHP'],
			],
		], $result->value);
		$this->assertSame(
			['items.[1].address.zip', 'tags.php.color'],
			array_map(static fn ($report): ?string => $report->toDebugPathString(), $result->getNoticeReports()),
		);
	}

	public function testFailFastFailureContainsNoticesFoundBeforeError(): void
	{
		$t = TypeSchema::get();
		$type = $t->list($t->arrayShape(['id' => $t->int()]));

		$result = $this->getProcessor()->parse([
			['id' => 1, 'extra' => true],
			['id' => 'x'],
			['id' => 'y'],
		], $type);

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertCount(1, $result->getReports());
		$this->assertSame('[0].extra', $result->getNoticeReports()[0]->toDebugPathString());
	}

	public function testProcessTreatsNoticesAsErrors(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape(['name' => $t->string()]);

		try {
			$this->getProcessor()->process(['name' => 'John', 'extra' => 1], $type);
			$this->fail('Expected AssertException was not thrown.');
		} catch (AssertException $exception) {
			$errors = $exception->getErrors();
			$this->assertCount(1, $errors);
			$this->assertInstanceOf(ExtraKey::class, $errors[0]->issue);
			$this->assertSame('This field was not expected.', (string) $errors[0]->message); // renderer of the processor
			$this->assertSame("✖ Extra key found.\n  → at extra", $exception->toPrettyString());
		}
	}

	public function testProcessFastTreatsNoticesAsErrors(): void
	{
		$t = TypeSchema::get();

		$this->expectException(AssertException::class);

		$this->getProcessor()->processFast(['name' => 'John', 'extra' => 1], $t->arrayShape(['name' => $t->string()]));
	}

	public function testProcessReportsNoticesTogetherWithErrors(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape(['name' => $t->string()]);

		try {
			$this->getProcessor()->process(['name' => 1, 'extra' => 1], $type);
			$this->fail('Expected AssertException was not thrown.');
		} catch (AssertException $exception) {
			$this->assertSame(
				[['name'], ['extra']],
				array_map(static fn ($report): array => $report->toArrayPath(), $exception->getErrors()),
			);
		}
	}

	public function testMatchesReturnsFalseForNotices(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape(['name' => $t->string()]);

		$this->assertTrue($this->getProcessor()->matches(['name' => 'John'], $type));
		$this->assertFalse($this->getProcessor()->matches(['name' => 'John', 'extra' => 1], $type));
	}

	public function testAfterReceivesCleanValueAndKeepsNotices(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape(['name' => $t->string()])
			->after(static fn (array $value): string => implode(',', array_keys($value)));

		$result = $this->getProcessor()->parse(['name' => 'John', 'extra' => 1], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame('name', $result->value);
		$this->assertTrue($result->hasNotices());
	}

	public function testValidateFailureKeepsNotices(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape(['name' => $t->string()])
			->validate(static fn (array $value): IssueNode => new CustomIssue('Always invalid.'));

		$result = $this->getProcessor()->parse(['name' => 'John', 'extra' => 1], $type);

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertSame('Always invalid.', (string) $result->getReports()[0]->message);
		$this->assertCount(1, $result->getNoticeReports());
	}

	public function testValidatePassKeepsNotices(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape(['name' => $t->string()])
			->validate(static fn (array $value): ?IssueNode => null);

		$result = $this->getProcessor()->parse(['name' => 'John', 'extra' => 1], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['name' => 'John'], $result->value);
		$this->assertTrue($result->hasNotices());
	}

	public function testNullableKeepsNotices(): void
	{
		$t = TypeSchema::get();
		$type = $t->nullable($t->arrayShape(['name' => $t->string()]));

		$result = $this->getProcessor()->parse(['name' => 'John', 'extra' => 1], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['name' => 'John'], $result->value);
		$this->assertTrue($result->hasNotices());
	}

	public function testCreateDefaultUsesEnglishRenderer(): void
	{
		$t = TypeSchema::get();

		try {
			TypeSchemaProcessor::createDefault()->process(['name' => 'John', 'extra' => 1], $t->arrayShape(['name' => $t->string()]));
			$this->fail('Expected AssertException was not thrown.');
		} catch (AssertException $exception) {
			$this->assertSame('This field is not allowed.', (string) $exception->getErrors()[0]->message);
		}
	}

}
