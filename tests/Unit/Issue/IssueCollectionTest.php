<?php declare(strict_types = 1);

namespace Tests\Unit\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\IssueCollection;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Issue\Path;

#[CoversClass(IssueCollection::class)]
final class IssueCollectionTest extends TestCase
{

	public function testGetIssuesMergesAllNodes(): void
	{
		$first = new CustomIssue('msg1');
		$second = new CustomIssue('msg2');
		$collection = new IssueCollection([$first, $second]);

		$issues = $collection->getIssues();

		$this->assertCount(2, $issues);
		$this->assertSame($first, $issues[0]->issue);
		$this->assertSame($second, $issues[1]->issue);
	}

	public function testGetIssuesPassesPath(): void
	{
		$collection = new IssueCollection([new CustomIssue('msg1'), new CustomIssue('msg2')]);
		$path = [new Path('root')];

		$issues = $collection->getIssues($path);

		$this->assertSame($path, $issues[0]->path);
		$this->assertSame($path, $issues[1]->path);
	}

	public function testGetIssuesWithNestedPaths(): void
	{
		$collection = new IssueCollection([
			new IssuePath(new CustomIssue('msg1'), new Path('field1')),
			new IssuePath(new CustomIssue('msg2'), new Path('field2')),
		]);

		$issues = $collection->getIssues();

		$this->assertSame('field1', $issues[0]->path[0]->path);
		$this->assertSame('field2', $issues[1]->path[0]->path);
	}

	public function testCreateReturnsSingleNodeDirectly(): void
	{
		$issue = new CustomIssue('msg');

		$this->assertSame($issue, IssueCollection::create([$issue]));
	}

	public function testCreateWrapsMultipleNodes(): void
	{
		$first = new CustomIssue('msg1');
		$second = new CustomIssue('msg2');

		$collection = IssueCollection::create([$first, $second]);

		$this->assertInstanceOf(IssueCollection::class, $collection);
		$this->assertSame([$first, $second], $collection->nodes);
	}

}
