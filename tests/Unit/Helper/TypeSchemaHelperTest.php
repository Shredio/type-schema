<?php declare(strict_types = 1);

namespace Tests\Unit\Helper;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Helper\TypeSchemaHelper;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\IssueCollection;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Issue\Path;
use Shredio\TypeSchema\Issue\Report\ErrorReport;
use Shredio\TypeSchema\Issue\Report\ErrorReportConfig;
use Shredio\TypeSchema\Issue\Report\TypeSchemaErrorFormatter;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
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
		$first = new CustomIssue('first', 'dev first');
		$second = new CustomIssue('second', 'dev second');

		$result = TypeSchemaHelper::mergeErrors($first, $second);

		self::assertInstanceOf(IssueCollection::class, $result);
		self::assertCount(2, $result->nodes);
		self::assertSame($first, $result->nodes[0]);
		self::assertSame($second, $result->nodes[1]);
	}

	public function testMergeErrorsWithPaths(): void
	{
		$first = new IssuePath(new CustomIssue('msg1', 'dev1'), new Path('field1'));
		$second = new IssuePath(new CustomIssue('msg2', 'dev2'), new Path('field2'));

		$issues = TypeSchemaHelper::mergeErrors($first, $second)->getIssues();

		self::assertCount(2, $issues);
		self::assertSame('field1', $issues[0]->path[0]->path);
		self::assertSame('field2', $issues[1]->path[0]->path);
	}

	public function testMergeErrorsCollectionAsFirstElement(): void
	{
		$first = new IssueCollection([
			new CustomIssue('a', 'dev a'),
			new CustomIssue('b', 'dev b'),
		]);
		$second = new CustomIssue('c', 'dev c');

		$reports = ErrorReport::fromIssues(TypeSchemaHelper::mergeErrors($first, $second));

		self::assertCount(3, $reports);
		self::assertSame('a', (string) $reports[0]->message);
		self::assertSame('b', (string) $reports[1]->message);
		self::assertSame('c', (string) $reports[2]->message);
	}

	public function testMergeErrorsBothCollections(): void
	{
		$first = new IssueCollection([
			new CustomIssue('a', 'dev a'),
			new CustomIssue('b', 'dev b'),
		]);
		$second = new IssueCollection([
			new CustomIssue('c', 'dev c'),
			new CustomIssue('d', 'dev d'),
		]);

		$reports = ErrorReport::fromIssues(TypeSchemaHelper::mergeErrors($first, $second));

		self::assertCount(4, $reports);
		self::assertSame('d', (string) $reports[3]->message);
		self::assertSame('dev d', (string) $reports[3]->messageForDeveloper);
	}

	public function testMergeErrorsPassesPathToIssues(): void
	{
		$result = TypeSchemaHelper::mergeErrors(new CustomIssue('msg1'), new CustomIssue('msg2'));
		$path = [new Path('root')];

		$issues = $result->getIssues($path);

		self::assertSame($path, $issues[0]->path);
		self::assertSame($path, $issues[1]->path);
	}

	public function testMergeErrorsReportsRespectConfig(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$result = TypeSchemaHelper::mergeErrors(
			new InvalidType($definition, 42),
			new InvalidType($definition, 'foo'),
		);

		$reports = ErrorReport::fromIssues($result, config: new ErrorReportConfig(exposeExpectedType: false));

		self::assertSame('The provided value is not valid.', (string) $reports[0]->message);
		self::assertSame('The provided value is not valid.', (string) $reports[1]->message);
	}

	public function testReindexShapeErrorMessage(): void
	{
		$schema = TypeSchemaHelper::reindexShape([
			'bar' => 'foo',
		], TypeSchema::get()->arrayShape([
			'foo' => TypeSchema::get()->string(),
			'other' => TypeSchema::get()->int(),
		]));

		$result = $this->getProcessor()->parse([
			'bar' => 15,
			'other' => 123,
		], $schema);

		self::assertInstanceOf(Failure::class, $result);
		self::assertSame(<<<'ERR'
✖ Invalid type int with value 15, expected string.
  → at bar
ERR, TypeSchemaErrorFormatter::prettyString($result));
	}

	public function testReindexShapeKeepsNoticesOfUnmappedKeys(): void
	{
		$schema = TypeSchemaHelper::reindexShape([
			'bar' => 'foo',
		], TypeSchema::get()->arrayShape([
			'foo' => TypeSchema::get()->string(),
		]));

		$result = $this->getProcessor()->parse([
			'bar' => 'hello',
			'unknown' => 1,
		], $schema);

		self::assertInstanceOf(Success::class, $result);
		self::assertSame(['foo' => 'hello'], $result->value);
		self::assertNotNull($result->notices);
		$notices = $result->notices->getIssues();
		self::assertCount(1, $notices);
		self::assertInstanceOf(ExtraKey::class, $notices[0]->issue);
		self::assertSame('unknown', $notices[0]->path[0]->path);
	}

}
