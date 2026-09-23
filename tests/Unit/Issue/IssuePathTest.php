<?php declare(strict_types = 1);

namespace Tests\Unit\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Issue\Path;

#[CoversClass(IssuePath::class)]
final class IssuePathTest extends TestCase
{

	public function testGetIssuesAppendsPath(): void
	{
		$issue = new CustomIssue('msg', 'dev msg');
		$issuePath = new IssuePath($issue, new Path('field'));

		$issues = $issuePath->getIssues();

		$this->assertCount(1, $issues);
		$this->assertSame($issue, $issues[0]->issue);
		$this->assertCount(1, $issues[0]->path);
		$this->assertSame('field', $issues[0]->path[0]->path);
	}

	public function testGetIssuesAppendsToExistingPath(): void
	{
		$issuePath = new IssuePath(new CustomIssue('msg'), new Path('child'));

		$issues = $issuePath->getIssues([new Path('parent')]);

		$this->assertCount(2, $issues[0]->path);
		$this->assertSame('parent', $issues[0]->path[0]->path);
		$this->assertSame('child', $issues[0]->path[1]->path);
	}

	public function testNestedPaths(): void
	{
		$inner = new IssuePath(new CustomIssue('msg'), new Path('level2'));
		$outer = new IssuePath($inner, new Path('level1'));

		$issues = $outer->getIssues();

		$this->assertCount(2, $issues[0]->path);
		$this->assertSame('level1', $issues[0]->path[0]->path);
		$this->assertSame('level2', $issues[0]->path[1]->path);
	}

	public function testWithPathReturnsNewInstance(): void
	{
		$issue = new CustomIssue('msg');
		$original = new IssuePath($issue, new Path('original'));
		$changed = $original->withPath(new Path('changed'));

		$this->assertSame('changed', $changed->path->path);
		$this->assertSame($issue, $changed->node);
		$this->assertSame('original', $original->path->path);
	}

}
