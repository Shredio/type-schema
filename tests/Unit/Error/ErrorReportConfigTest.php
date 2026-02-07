<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Error\ErrorReportConfig;

#[CoversClass(ErrorReportConfig::class)]
final class ErrorReportConfigTest extends TestCase
{

	public function testDefaultExposeExpectedType(): void
	{
		$config = new ErrorReportConfig();

		$this->assertTrue($config->exposeExpectedType);
	}

	public function testCustomExposeExpectedType(): void
	{
		$config = new ErrorReportConfig(exposeExpectedType: false);

		$this->assertFalse($config->exposeExpectedType);
	}

}
