<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Error\ErrorCollection;
use Shredio\TypeSchema\Error\ErrorInvalidType;
use Shredio\TypeSchema\Error\ErrorMessage;
use Shredio\TypeSchema\Error\ErrorPath;
use Shredio\TypeSchema\Error\ErrorReportConfig;
use Shredio\TypeSchema\Error\Path;

#[CoversClass(ErrorCollection::class)]
final class ErrorCollectionTest extends TestCase
{

	public function testGetReportsMergesAllErrors(): void
	{
		$error1 = new ErrorMessage('msg1', 'dev1');
		$error2 = new ErrorMessage('msg2', 'dev2');
		$collection = new ErrorCollection([$error1, $error2]);

		$reports = $collection->getReports();

		$this->assertCount(2, $reports);
		$this->assertSame('msg1', (string) $reports[0]->message);
		$this->assertSame('msg2', (string) $reports[1]->message);
	}

	public function testGetReportsPassesPath(): void
	{
		$error1 = new ErrorMessage('msg1', 'dev1');
		$error2 = new ErrorMessage('msg2', 'dev2');
		$collection = new ErrorCollection([$error1, $error2]);

		$path = [new Path('root')];
		$reports = $collection->getReports($path);

		$this->assertCount(2, $reports);
		$this->assertSame($path, $reports[0]->path);
		$this->assertSame($path, $reports[1]->path);
	}

	public function testGetReportsWithNestedPaths(): void
	{
		$error1 = new ErrorPath(new ErrorMessage('msg1', 'dev1'), new Path('field1'));
		$error2 = new ErrorPath(new ErrorMessage('msg2', 'dev2'), new Path('field2'));
		$collection = new ErrorCollection([$error1, $error2]);

		$reports = $collection->getReports();

		$this->assertCount(2, $reports);
		$this->assertSame('field1', $reports[0]->path[0]->path);
		$this->assertSame('field2', $reports[1]->path[0]->path);
	}

	public function testGetReportsPassesConfigToChildren(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = new ErrorInvalidType(
			$definition,
			fn (?string $type): string => sprintf('Expected %s.', $type ?? 'nothing'),
			42,
		);
		$collection = new ErrorCollection([$error]);

		$config = new ErrorReportConfig(exposeExpectedType: false);
		$reports = $collection->getReports([], $config);

		$this->assertSame('Expected nothing.', (string) $reports[0]->message);
	}

	public function testCollectionProperty(): void
	{
		$error1 = new ErrorMessage('msg1', 'dev1');
		$error2 = new ErrorMessage('msg2', 'dev2');
		$collection = new ErrorCollection([$error1, $error2]);

		$this->assertCount(2, $collection->collection);
		$this->assertSame($error1, $collection->collection[0]);
		$this->assertSame($error2, $collection->collection[1]);
	}

}
