<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\Issue\ExtraKey;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Result\Success;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\Types\ArrayShapeType;
use Shredio\TypeSchema\Types\IntType;
use Shredio\TypeSchema\Types\MixedType;
use Shredio\TypeSchema\Types\OptionalType;
use Shredio\TypeSchema\Types\StringType;
use stdClass;
use Tests\TypeTestCase;

final class ArrayShapeTypeTest extends TypeTestCase
{

	protected function getValidValues(): iterable
	{
		// Simple shape with required string and int
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
			'age' => new IntType(),
		]), ['array', 'string', 'int']);
		yield 'basic shape' => ['name' => 'John', 'age' => 30];
		yield 'shape with different values' => ['name' => 'Jane', 'age' => 25];

		// Shape with optional field
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
			'nickname' => new OptionalType(new StringType()),
		]), ['array', 'string']);
		yield 'shape without optional field' => ['name' => 'John'];
		yield 'shape with optional field present' => ['name' => 'John', 'nickname' => 'Johnny'];

		// Shape with all optional fields - empty
		yield $this->typeToTest(new ArrayShapeType([
			'first' => new OptionalType(new StringType()),
			'second' => new OptionalType(new IntType()),
		]), ['array']);
		yield 'empty shape when all optional' => [];

		// Shape with all optional fields - partial
		yield $this->typeToTest(new ArrayShapeType([
			'first' => new OptionalType(new StringType()),
			'second' => new OptionalType(new IntType()),
		]), ['array', 'string']);
		yield 'partial optional shape' => ['first' => 'hello'];

		// Shape with all optional fields - full
		yield $this->typeToTest(new ArrayShapeType([
			'first' => new OptionalType(new StringType()),
			'second' => new OptionalType(new IntType()),
		]), ['array', 'string', 'int']);
		yield 'full optional shape' => ['first' => 'hello', 'second' => 42];

		// Shape with integer keys
		yield $this->typeToTest(new ArrayShapeType([
			0 => new StringType(),
			1 => new IntType(),
		]), ['array', 'string', 'int']);
		yield 'integer keyed shape' => [0 => 'value', 1 => 100];

		// Open shape with mixed rest
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
		], new MixedType()), ['array', 'string']);
		yield 'open shape with extra key' => ['name' => 'John', 'extra' => 'allowed'];
		yield 'open shape with multiple extra keys' => ['name' => 'John', 'extra1' => 'a', 'extra2' => 'b'];

		// Open shape with typed rest
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
		], new IntType()), ['array', 'string', 'int']);
		yield 'open shape with typed extra key' => ['name' => 'John', 'extra' => 1];

		// Complex mixed shape without optional metadata
		yield $this->typeToTest(new ArrayShapeType([
			'id' => new IntType(),
			'name' => new StringType(),
			'metadata' => new OptionalType(new ArrayShapeType([
				'created' => new StringType(),
			])),
		]), ['array', 'int', 'string']);
		yield 'complex shape without optional' => ['id' => 1, 'name' => 'Test'];
	}

	protected function getInvalidValues(): iterable
	{
		// Required fields missing
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
			'age' => new IntType(),
		]));
		yield 'missing required field name' => ['age' => 30];
		yield 'missing required field age' => ['name' => 'John'];
		yield 'empty array when fields required' => [];

		// Wrong types for fields
		yield 'wrong type for name' => ['name' => 123, 'age' => 30];
		yield 'wrong type for age' => ['name' => 'John', 'age' => 'thirty'];
		yield 'null for required field' => ['name' => null, 'age' => 30];

		// Extra keys in a closed shape produce notices, matches() treats them as errors
		yield 'extra key in closed shape' => ['name' => 'John', 'age' => 30, 'extra' => 'rejected'];
		yield 'multiple extra keys' => ['name' => 'John', 'age' => 30, 'a' => 1, 'b' => 2];

		// Non-array values
		yield 'string value' => 'hello';
		yield 'integer value' => 42;
		yield 'float value' => 3.14;
		yield 'boolean true' => true;
		yield 'boolean false' => false;
		yield 'null value' => null;
		yield 'stdClass object' => new stdClass();
		yield 'callable object' => $this->callableObject('hello');
		yield 'closure' => function () {};
		yield 'anonymous class' => new class {};
		yield 'resource' => $this->resource();

		// Optional field with wrong type
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
			'nickname' => new OptionalType(new StringType()),
		]));
		yield 'optional field with wrong type' => ['name' => 'John', 'nickname' => 123];

		// Nested shape validation
		yield $this->typeToTest(new ArrayShapeType([
			'user' => new ArrayShapeType([
				'name' => new StringType(),
			]),
		]));
		yield 'nested shape with wrong type' => ['user' => 'not-an-array'];
		yield 'nested shape with missing field' => ['user' => []];
		yield 'nested shape with extra field rejected' => ['user' => ['name' => 'John', 'extra' => 'value']];

		// Open shape with typed rest
		yield $this->typeToTest(new ArrayShapeType([
			'name' => new StringType(),
		], new IntType()));
		yield 'open shape with wrongly typed extra key' => ['name' => 'John', 'extra' => 'not-int'];
	}

	public function testExtraKeysAreRemovedAndReportedAsNotices(): void
	{
		$type = new ArrayShapeType([
			'name' => new StringType(),
		]);

		$result = $this->getProcessor()->parse(['name' => 'John', 'extra' => 'should be removed'], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['name' => 'John'], $result->value);
		$this->assertNotNull($result->notices);
		$notices = $result->notices->getIssues();
		$this->assertCount(1, $notices);
		$this->assertInstanceOf(ExtraKey::class, $notices[0]->issue);
		$this->assertSame('extra', $notices[0]->path[0]->path);
	}

	public function testOpenShapeKeepsExtraKeys(): void
	{
		$type = new ArrayShapeType([
			'name' => new StringType(),
		], new MixedType());

		$result = $this->getProcessor()->parse(['name' => 'John', 'extra' => ['nested' => true]], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertSame(['name' => 'John', 'extra' => ['nested' => true]], $result->value);
		$this->assertFalse($result->hasNotices());
	}

	public function testOpenShapeParsesExtraValuesWithRestType(): void
	{
		$t = TypeSchema::get();
		$type = $t->arrayShape(['name' => $t->string()], rest: $t->int());

		$result = $this->getProcessor()->parse(['name' => 'John', 'extra' => 'abc'], $type);

		$this->assertInstanceOf(Failure::class, $result);
		$issues = $result->errors->getIssues();
		$this->assertCount(1, $issues);
		$this->assertInstanceOf(InvalidType::class, $issues[0]->issue);
		$this->assertSame('extra', $issues[0]->path[0]->path);
	}

	public function testOptionalKeyIsNotExtraKey(): void
	{
		$type = new ArrayShapeType([
			'name' => new StringType(),
			'nickname' => new OptionalType(new StringType()),
		]);

		$result = $this->getProcessor()->parse(['name' => 'John', 'nickname' => 'Johnny'], $type);

		$this->assertInstanceOf(Success::class, $result);
		$this->assertFalse($result->hasNotices());
	}

	public function testNoticesAreKeptAlongsideErrors(): void
	{
		$type = new ArrayShapeType([
			'name' => new StringType(),
			'age' => new IntType(),
		]);

		$result = $this->getProcessor()->parse(['extra' => 1, 'name' => 'John', 'age' => 'x'], $type, collectErrors: true);

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertSame(['age'], $result->getReports()[0]->toArrayPath());
		$notices = $result->getNoticeReports();
		$this->assertCount(1, $notices);
		$this->assertSame(['extra'], $notices[0]->toArrayPath());
	}

	public function testNoticesUseIdentifiedPath(): void
	{
		$t = TypeSchema::get();
		$type = $t->list($t->arrayShape(['id' => $t->int()], identifier: 'id'));

		$result = $this->getProcessor()->parse([['id' => 5, 'extra' => true]], $type);

		$this->assertInstanceOf(Success::class, $result);
		$notices = $result->getNoticeReports();
		$this->assertCount(1, $notices);
		$this->assertSame('[0].extra', $notices[0]->toDebugPathString());
		$this->assertNull($notices[0]->toIdentifiedPath()); // list index is not identified
		$this->assertSame(5, $notices[0]->path[1]->identified?->value);
	}

}
