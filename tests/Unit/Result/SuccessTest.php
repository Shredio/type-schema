<?php declare(strict_types = 1);

namespace Tests\Unit\Result;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Issue\Path;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;

#[CoversClass(Success::class)]
final class SuccessTest extends TestCase
{

	public function testWithoutNotices(): void
	{
		$success = new Success(['name' => 'John']);

		$this->assertFalse($success->hasNotices());
		$this->assertSame([], $success->getNoticeReports());
		$this->assertSame($success, $success->withNoticesAsErrors());
	}

	public function testWithNotices(): void
	{
		$notices = new IssuePath(new ExtraKey(), new Path('extra'));
		$success = new Success(['name' => 'John'], $notices);

		$this->assertTrue($success->hasNotices());
		$reports = $success->getNoticeReports();
		$this->assertCount(1, $reports);
		$this->assertSame(['extra'], $reports[0]->toArrayPath());
		$this->assertSame('Extra key found.', (string) $reports[0]->messageForDeveloper);
	}

	public function testWithNoticesAsErrorsCreatesFailure(): void
	{
		$notices = new IssuePath(new ExtraKey(), new Path('extra'));
		$success = new Success(['name' => 'John'], $notices);

		$failure = $success->withNoticesAsErrors();

		$this->assertInstanceOf(Failure::class, $failure);
		$this->assertSame($notices, $failure->errors);
		$this->assertNull($failure->notices);
	}

}
