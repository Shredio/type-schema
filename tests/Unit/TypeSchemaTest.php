<?php declare(strict_types = 1);

namespace Tests\Unit;

use Shredio\TypeSchema\TypeSchema;
use Tests\TestCase;

final class TypeSchemaTest extends TestCase
{

	public function testOneElement(): void
	{
		$isString = TypeSchema::get()->string();

		$this->assertTrue($this->getProcessor()->matches('hello', $isString));
		$this->assertFalse($this->getProcessor()->matches(123, $isString));
	}

	public function testComplexSchema(): void
	{
		$schema = $this->createLargeTypeSchema();

		$this->assertTrue($this->getProcessor()->matches($this->getValidValuesForLargeSchema(), $schema));
		$this->assertSame($this->getValidValuesForLargeSchema(), $this->getProcessor()->process($this->getValidValuesForLargeSchema(), $schema));
	}

}
