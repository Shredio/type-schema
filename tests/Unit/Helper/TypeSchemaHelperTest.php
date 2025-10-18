<?php declare(strict_types = 1);

namespace Tests\Unit\Helper;

use Shredio\TypeSchema\Error\ErrorElement;
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
