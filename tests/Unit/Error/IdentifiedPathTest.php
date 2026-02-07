<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Error\IdentifiedPath;

#[CoversClass(IdentifiedPath::class)]
final class IdentifiedPathTest extends TestCase
{

	public function testConstructStoresValue(): void
	{
		$identified = new IdentifiedPath('value');

		$this->assertSame('value', $identified->value);
	}

	public function testConstructStoresMixedValue(): void
	{
		$identified = new IdentifiedPath(42);

		$this->assertSame(42, $identified->value);
	}

	public function testCreateReturnsInstanceWhenIdentifierExists(): void
	{
		$result = IdentifiedPath::create('id', ['id' => 123, 'name' => 'test']);

		$this->assertNotNull($result);
		$this->assertSame(123, $result->value);
	}

	public function testCreateReturnsNullWhenIdentifierIsNull(): void
	{
		$result = IdentifiedPath::create(null, ['id' => 123]);

		$this->assertNull($result);
	}

	public function testCreateReturnsNullWhenIdentifierNotInValues(): void
	{
		$result = IdentifiedPath::create('missing', ['id' => 123]);

		$this->assertNull($result);
	}

	public function testCreateWithNullValue(): void
	{
		$result = IdentifiedPath::create('key', ['key' => null]);

		$this->assertNotNull($result);
		$this->assertNull($result->value);
	}

}
