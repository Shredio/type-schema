<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Error\ErrorReport;
use Shredio\TypeSchema\Error\IdentifiedPath;
use Shredio\TypeSchema\Error\Path;

#[CoversClass(ErrorReport::class)]
final class ErrorReportTest extends TestCase
{

	public function testToArrayPathEmpty(): void
	{
		$report = new ErrorReport('msg', 'dev');

		$this->assertSame([], $report->toArrayPath());
	}

	public function testToArrayPathWithStringPaths(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('address'),
			new Path('street'),
		]);

		$this->assertSame(['address', 'street'], $report->toArrayPath());
	}

	public function testToArrayPathWithIntPaths(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('items'),
			new Path(0),
			new Path('name'),
		]);

		$this->assertSame(['items', 0, 'name'], $report->toArrayPath());
	}

	public function testToDebugPathStringEmpty(): void
	{
		$report = new ErrorReport('msg', 'dev');

		$this->assertNull($report->toDebugPathString());
	}

	public function testToDebugPathStringWithSimpleKeys(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('address'),
			new Path('street'),
		]);

		$this->assertSame('address.street', $report->toDebugPathString());
	}

	public function testToDebugPathStringWithIntegerIndex(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('items'),
			new Path(0),
			new Path('name'),
		]);

		$this->assertSame('items.[0].name', $report->toDebugPathString());
	}

	public function testToDebugPathStringEscapesSpecialKeys(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('my-field'),
		]);

		$this->assertSame("'my-field'", $report->toDebugPathString());
	}

	public function testToDebugPathStringEscapesKeyWithSpaces(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('some field'),
		]);

		$this->assertSame("'some field'", $report->toDebugPathString());
	}

	public function testToDebugPathStringDoesNotEscapeSimpleKeys(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('_valid_key'),
			new Path('anotherKey123'),
		]);

		$this->assertSame('_valid_key.anotherKey123', $report->toDebugPathString());
	}

	public function testToDebugPathStringEscapesSingleQuoteInKey(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path("it's"),
		]);

		$this->assertSame("'it\\'s'", $report->toDebugPathString());
	}

	public function testToIdentifiedPathEmpty(): void
	{
		$report = new ErrorReport('msg', 'dev');

		$this->assertNull($report->toIdentifiedPath());
	}

	public function testToIdentifiedPathReturnsNullWhenNoIdentifiers(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('field'),
		]);

		$this->assertNull($report->toIdentifiedPath());
	}

	public function testToIdentifiedPathWithIdentifiers(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('users', new IdentifiedPath(42)),
			new Path('name', new IdentifiedPath('John')),
		]);

		$this->assertSame('42 -> "John"', $report->toIdentifiedPath());
	}

	public function testToIdentifiedPathWithCustomSeparator(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('users', new IdentifiedPath(42)),
			new Path('name', new IdentifiedPath('John')),
		]);

		$this->assertSame('42 / "John"', $report->toIdentifiedPath(' / '));
	}

	public function testToIdentifiedPathReturnsNullIfAnyPathMissesIdentifier(): void
	{
		$report = new ErrorReport('msg', 'dev', [
			new Path('users', new IdentifiedPath(42)),
			new Path('name'),
		]);

		$this->assertNull($report->toIdentifiedPath());
	}

}
