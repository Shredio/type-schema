<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Error\IdentifiedPath;
use Shredio\TypeSchema\Error\Path;

#[CoversClass(Path::class)]
final class PathTest extends TestCase
{

	public function testConstructWithStringPath(): void
	{
		$path = new Path('name');

		$this->assertSame('name', $path->path);
		$this->assertNull($path->identified);
	}

	public function testConstructWithIntPath(): void
	{
		$path = new Path(0);

		$this->assertSame(0, $path->path);
		$this->assertNull($path->identified);
	}

	public function testConstructWithIdentifiedPath(): void
	{
		$identified = new IdentifiedPath('abc');
		$path = new Path('field', $identified);

		$this->assertSame('field', $path->path);
		$this->assertSame($identified, $path->identified);
	}

	public function testWithPathReturnsNewInstanceWithChangedPath(): void
	{
		$identified = new IdentifiedPath('abc');
		$original = new Path('original', $identified);
		$changed = $original->withPath('changed');

		$this->assertSame('changed', $changed->path);
		$this->assertSame($identified, $changed->identified);
		$this->assertSame('original', $original->path);
	}

	public function testWithPathAcceptsIntPath(): void
	{
		$original = new Path('field');
		$changed = $original->withPath(5);

		$this->assertSame(5, $changed->path);
		$this->assertNull($changed->identified);
	}

}
