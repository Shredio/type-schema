<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Error\ErrorInvalidType;
use Shredio\TypeSchema\Error\ErrorMessage;
use Shredio\TypeSchema\Error\ErrorPath;
use Shredio\TypeSchema\Error\ErrorReportConfig;
use Shredio\TypeSchema\Error\Path;

#[CoversClass(ErrorPath::class)]
final class ErrorPathTest extends TestCase
{

	public function testGetReportsAppendsPath(): void
	{
		$innerError = new ErrorMessage('msg', 'dev msg');
		$errorPath = new ErrorPath($innerError, new Path('field'));

		$reports = $errorPath->getReports();

		$this->assertCount(1, $reports);
		$this->assertCount(1, $reports[0]->path);
		$this->assertSame('field', $reports[0]->path[0]->path);
	}

	public function testGetReportsAppendsToExistingPath(): void
	{
		$innerError = new ErrorMessage('msg', 'dev msg');
		$errorPath = new ErrorPath($innerError, new Path('child'));

		$reports = $errorPath->getReports([new Path('parent')]);

		$this->assertCount(1, $reports);
		$this->assertCount(2, $reports[0]->path);
		$this->assertSame('parent', $reports[0]->path[0]->path);
		$this->assertSame('child', $reports[0]->path[1]->path);
	}

	public function testNestedErrorPaths(): void
	{
		$innerError = new ErrorMessage('msg', 'dev msg');
		$innerPath = new ErrorPath($innerError, new Path('level2'));
		$outerPath = new ErrorPath($innerPath, new Path('level1'));

		$reports = $outerPath->getReports();

		$this->assertCount(1, $reports);
		$this->assertCount(2, $reports[0]->path);
		$this->assertSame('level1', $reports[0]->path[0]->path);
		$this->assertSame('level2', $reports[0]->path[1]->path);
	}

	public function testGetReportsPassesConfigToChild(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$innerError = new ErrorInvalidType(
			$definition,
			fn (?string $type): string => sprintf('Expected %s.', $type ?? 'nothing'),
			42,
		);
		$errorPath = new ErrorPath($innerError, new Path('field'));

		$config = new ErrorReportConfig(exposeExpectedType: false);
		$reports = $errorPath->getReports([], $config);

		$this->assertSame('Expected nothing.', (string) $reports[0]->message);
	}

	public function testWithPathReturnsNewInstance(): void
	{
		$innerError = new ErrorMessage('msg', 'dev msg');
		$original = new ErrorPath($innerError, new Path('original'));
		$changed = $original->withPath(new Path('changed'));

		$this->assertSame('changed', $changed->path->path);
		$this->assertSame($innerError, $changed->error);
		$this->assertSame('original', $original->path->path);
	}

}
