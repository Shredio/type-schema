<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Error\ErrorCollection;
use Shredio\TypeSchema\Error\ErrorMessage;
use Shredio\TypeSchema\Error\ErrorPath;
use Shredio\TypeSchema\Error\IdentifiedPath;
use Shredio\TypeSchema\Error\Path;
use Shredio\TypeSchema\Error\TypeSchemaErrorFormatter;

#[CoversClass(TypeSchemaErrorFormatter::class)]
final class TypeSchemaErrorFormatterTest extends TestCase
{

	public function testPrettyStringWithSingleError(): void
	{
		$error = new ErrorMessage('User msg', 'Developer msg');

		$result = TypeSchemaErrorFormatter::prettyString($error);

		$this->assertSame('✖ Developer msg', $result);
	}

	public function testPrettyStringWithPath(): void
	{
		$error = new ErrorPath(
			new ErrorMessage('User msg', 'Developer msg'),
			new Path('name'),
		);

		$result = TypeSchemaErrorFormatter::prettyString($error);

		$this->assertStringContainsString('Developer msg', $result);
		$this->assertStringContainsString('at name', $result);
	}

	public function testPrettyStringWithIdentifiedPath(): void
	{
		$error = new ErrorPath(
			new ErrorMessage('User msg', 'Developer msg'),
			new Path('users', new IdentifiedPath(42)),
		);

		$result = TypeSchemaErrorFormatter::prettyString($error);

		$this->assertStringContainsString('Developer msg', $result);
		$this->assertStringContainsString('at users', $result);
		$this->assertStringContainsString('for value 42', $result);
	}

	public function testPrettyStringWithMultipleErrors(): void
	{
		$error = new ErrorCollection([
			new ErrorPath(new ErrorMessage('msg1', 'dev1'), new Path('field1')),
			new ErrorPath(new ErrorMessage('msg2', 'dev2'), new Path('field2')),
		]);

		$result = TypeSchemaErrorFormatter::prettyString($error);

		$this->assertStringContainsString('dev1', $result);
		$this->assertStringContainsString('dev2', $result);
		$this->assertStringContainsString('field1', $result);
		$this->assertStringContainsString('field2', $result);
	}

	public function testPrettyStringWithCustomStyles(): void
	{
		$error = new ErrorPath(
			new ErrorMessage('User msg', 'Developer msg'),
			new Path('name'),
		);

		$result = TypeSchemaErrorFormatter::prettyString($error, '- ', '> ');

		$this->assertStringStartsWith('- Developer msg', $result);
		$this->assertStringContainsString('> at name', $result);
	}

	public function testPrettyStringWithoutPath(): void
	{
		$error = new ErrorMessage('User msg', 'Developer msg');

		$result = TypeSchemaErrorFormatter::prettyString($error, '', '');

		$this->assertSame('Developer msg', $result);
	}

}
