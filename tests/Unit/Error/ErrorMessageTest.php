<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Error\ErrorMessage;
use Shredio\TypeSchema\Error\ErrorReport;
use Shredio\TypeSchema\Error\Path;

#[CoversClass(ErrorMessage::class)]
final class ErrorMessageTest extends TestCase
{

	public function testGetReportsWithoutPath(): void
	{
		$error = new ErrorMessage('User message', 'Developer message');
		$reports = $error->getReports();

		$this->assertCount(1, $reports);
		$this->assertSame('User message', (string) $reports[0]->message);
		$this->assertSame('Developer message', (string) $reports[0]->messageForDeveloper);
		$this->assertSame([], $reports[0]->path);
	}

	public function testGetReportsWithPath(): void
	{
		$error = new ErrorMessage('User message', 'Developer message');
		$path = [new Path('field'), new Path(0)];
		$reports = $error->getReports($path);

		$this->assertCount(1, $reports);
		$this->assertSame($path, $reports[0]->path);
	}

	public function testGetReportsWithStringable(): void
	{
		$message = new class implements \Stringable {
			public function __toString(): string
			{
				return 'Stringable message';
			}
		};

		$devMessage = new class implements \Stringable {
			public function __toString(): string
			{
				return 'Stringable dev message';
			}
		};

		$error = new ErrorMessage($message, $devMessage);
		$reports = $error->getReports();

		$this->assertSame('Stringable message', (string) $reports[0]->message);
		$this->assertSame('Stringable dev message', (string) $reports[0]->messageForDeveloper);
	}

}
