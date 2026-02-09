<?php declare(strict_types = 1);

namespace Tests\Unit\Helper;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Error\ErrorCollection;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorInvalidType;
use Shredio\TypeSchema\Error\ErrorMessage;
use Shredio\TypeSchema\Error\ErrorPath;
use Shredio\TypeSchema\Error\ErrorReportConfig;
use Shredio\TypeSchema\Error\Path;
use Shredio\TypeSchema\Error\TypeSchemaErrorFormatter;
use Shredio\TypeSchema\Helper\TypeSchemaHelper;
use Shredio\TypeSchema\TypeSchema;
use Tests\TestCase;

final class TypeSchemaHelperTest extends TestCase
{

	public function testReindexShape(): void
	{
		$schema = TypeSchemaHelper::reindexShape([
			'bar' => 'foo',
		], TypeSchema::get()->arrayShape([
			'foo' => TypeSchema::get()->string(),
			'other' => TypeSchema::get()->int(),
		]));

		self::assertSame([
			'foo' => 'hello',
			'other' => 123,
		], $this->validStrictParse($schema, [
			'bar' => 'hello',
			'other' => 123,
		]));
	}

	public function testMergeErrorsReturnsCollection(): void
	{
		$first = new ErrorMessage('first', 'dev first');
		$second = new ErrorMessage('second', 'dev second');

		$result = TypeSchemaHelper::mergeErrors($first, $second);

		self::assertInstanceOf(ErrorCollection::class, $result);
		self::assertCount(2, $result->collection);
		self::assertSame($first, $result->collection[0]);
		self::assertSame($second, $result->collection[1]);
	}

	public function testMergeErrorsReportsAreMerged(): void
	{
		$first = new ErrorMessage('msg1', 'dev1');
		$second = new ErrorMessage('msg2', 'dev2');

		$result = TypeSchemaHelper::mergeErrors($first, $second);
		$reports = $result->getReports();

		self::assertCount(2, $reports);
		self::assertSame('msg1', (string) $reports[0]->message);
		self::assertSame('msg2', (string) $reports[1]->message);
	}

	public function testMergeErrorsWithPaths(): void
	{
		$first = new ErrorPath(new ErrorMessage('msg1', 'dev1'), new Path('field1'));
		$second = new ErrorPath(new ErrorMessage('msg2', 'dev2'), new Path('field2'));

		$result = TypeSchemaHelper::mergeErrors($first, $second);
		$reports = $result->getReports();

		self::assertCount(2, $reports);
		self::assertSame('field1', $reports[0]->path[0]->path);
		self::assertSame('field2', $reports[1]->path[0]->path);
	}

	public function testMergeErrorsCollectionAsFirstElement(): void
	{
		$first = new ErrorCollection([
			new ErrorMessage('a', 'dev a'),
			new ErrorMessage('b', 'dev b'),
		]);
		$second = new ErrorMessage('c', 'dev c');

		$result = TypeSchemaHelper::mergeErrors($first, $second);
		$reports = $result->getReports();

		self::assertCount(3, $reports);
		self::assertSame('a', (string) $reports[0]->message);
		self::assertSame('b', (string) $reports[1]->message);
		self::assertSame('c', (string) $reports[2]->message);
	}

	public function testMergeErrorsCollectionAsSecondElement(): void
	{
		$first = new ErrorMessage('a', 'dev a');
		$second = new ErrorCollection([
			new ErrorMessage('b', 'dev b'),
			new ErrorMessage('c', 'dev c'),
		]);

		$result = TypeSchemaHelper::mergeErrors($first, $second);
		$reports = $result->getReports();

		self::assertCount(3, $reports);
		self::assertSame('a', (string) $reports[0]->message);
		self::assertSame('b', (string) $reports[1]->message);
		self::assertSame('c', (string) $reports[2]->message);
	}

	public function testMergeErrorsBothCollections(): void
	{
		$first = new ErrorCollection([
			new ErrorMessage('a', 'dev a'),
			new ErrorMessage('b', 'dev b'),
		]);
		$second = new ErrorCollection([
			new ErrorMessage('c', 'dev c'),
			new ErrorMessage('d', 'dev d'),
		]);

		$result = TypeSchemaHelper::mergeErrors($first, $second);
		$reports = $result->getReports();

		self::assertCount(4, $reports);
		self::assertSame('a', (string) $reports[0]->message);
		self::assertSame('b', (string) $reports[1]->message);
		self::assertSame('c', (string) $reports[2]->message);
		self::assertSame('d', (string) $reports[3]->message);
	}

	public function testMergeErrorsWithErrorInvalidType(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$first = new ErrorInvalidType(
			$definition,
			fn (?string $type): string => sprintf('Expected %s.', $type ?? 'unknown'),
			42,
		);
		$second = new ErrorMessage('other error', 'dev other');

		$result = TypeSchemaHelper::mergeErrors($first, $second);
		$reports = $result->getReports();

		self::assertCount(2, $reports);
		self::assertSame('Expected string.', (string) $reports[0]->message);
		self::assertSame('other error', (string) $reports[1]->message);
	}

	public function testMergeErrorsPassesPathToReports(): void
	{
		$first = new ErrorMessage('msg1', 'dev1');
		$second = new ErrorMessage('msg2', 'dev2');

		$result = TypeSchemaHelper::mergeErrors($first, $second);
		$path = [new Path('root')];
		$reports = $result->getReports($path);

		self::assertCount(2, $reports);
		self::assertSame($path, $reports[0]->path);
		self::assertSame($path, $reports[1]->path);
	}

	public function testMergeErrorsPassesConfigToReports(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$first = new ErrorInvalidType(
			$definition,
			fn (?string $type): string => sprintf('Expected %s.', $type ?? 'unknown'),
			42,
		);
		$second = new ErrorInvalidType(
			$definition,
			fn (?string $type): string => sprintf('Want %s.', $type ?? 'unknown'),
			'foo',
		);

		$result = TypeSchemaHelper::mergeErrors($first, $second);
		$config = new ErrorReportConfig(exposeExpectedType: false);
		$reports = $result->getReports([], $config);

		self::assertCount(2, $reports);
		self::assertSame('Expected unknown.', (string) $reports[0]->message);
		self::assertSame('Want unknown.', (string) $reports[1]->message);
	}

	public function testMergeErrorsDeveloperMessages(): void
	{
		$first = new ErrorMessage('user1', 'developer1');
		$second = new ErrorMessage('user2', 'developer2');

		$result = TypeSchemaHelper::mergeErrors($first, $second);
		$reports = $result->getReports();

		self::assertCount(2, $reports);
		self::assertSame('developer1', (string) $reports[0]->messageForDeveloper);
		self::assertSame('developer2', (string) $reports[1]->messageForDeveloper);
	}

	public function testReindexShapeErrorMessage(): void
	{
		$schema = TypeSchemaHelper::reindexShape([
			'bar' => 'foo',
		], TypeSchema::get()->arrayShape([
			'foo' => TypeSchema::get()->string(),
			'other' => TypeSchema::get()->int(),
		]));

		$error = $this->getProcessor()->parse([
			'bar' => 15,
			'other' => 123,
		], $schema);

		self::assertInstanceOf(ErrorElement::class, $error);
		self::assertSame(<<<'ERR'
✖ Invalid type int with value 15, expected string.
  → at bar
ERR, TypeSchemaErrorFormatter::prettyString($error));
	}

}
