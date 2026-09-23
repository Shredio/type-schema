<?php declare(strict_types = 1);

namespace Tests\Unit\Result;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\IdentifiedPath;
use Shredio\TypeSchema\Issue\MissingKey;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\IssueCollector;
use Shredio\TypeSchema\Result\WithNotices;

#[CoversClass(IssueCollector::class)]
final class IssueCollectorTest extends TestCase
{

	public function testCreateResultWithoutIssuesReturnsValue(): void
	{
		$this->assertSame(['a' => 1], (new IssueCollector())->createResult(['a' => 1]));
	}

	public function testCreateResultWithNoticesReturnsWithNotices(): void
	{
		$collector = new IssueCollector();
		$collector->addNotice(new ExtraKey(), 'extra');

		$result = $collector->createResult(['a' => 1]);

		$this->assertInstanceOf(WithNotices::class, $result);
		$this->assertSame(['a' => 1], $result->value);
		$this->assertSame('extra', $result->notices->getIssues()[0]->path[0]->path);
	}

	public function testCreateResultWithErrorsReturnsFailureWithNotices(): void
	{
		$collector = new IssueCollector();
		$collector->addNotice(new ExtraKey(), 'extra');
		$collector->addError(new MissingKey(), 'name', new IdentifiedPath(5));

		$result = $collector->createResult(['a' => 1]);

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertTrue($collector->hasErrors());
		$error = $result->errors->getIssues()[0];
		$this->assertInstanceOf(MissingKey::class, $error->issue);
		$this->assertSame('name', $error->path[0]->path);
		$this->assertSame(5, $error->path[0]->identified?->value);
		$this->assertNotNull($result->notices);
	}

	public function testAddChildPrefixesErrorsAndNotices(): void
	{
		$collector = new IssueCollector();
		$collector->addChild(new Failure(new MissingKey(), new ExtraKey()), 0);
		$collector->addChild(new WithNotices('value', new ExtraKey()), 1);

		$failure = $collector->createFailure();

		$this->assertSame([0], array_map(static fn ($path) => $path->path, $failure->errors->getIssues()[0]->path));
		$this->assertNotNull($failure->notices);
		$notices = $failure->notices->getIssues();
		$this->assertCount(2, $notices);
		$this->assertSame(0, $notices[0]->path[0]->path);
		$this->assertSame(1, $notices[1]->path[0]->path);
	}

	public function testCreateFailureWithoutErrorsThrows(): void
	{
		$collector = new IssueCollector();
		$collector->addNotice(new ExtraKey(), 'extra');

		$this->expectException(LogicException::class);

		$collector->createFailure();
	}

}
