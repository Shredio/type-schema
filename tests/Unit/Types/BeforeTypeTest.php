<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use Shredio\TypeSchema\TypeSchema;
use Tests\TestCase;

final class BeforeTypeTest extends TestCase
{

	public function testBeforeString(): void
	{
		$s = TypeSchema::get();

		self::assertSame('string', $this->validStrictParse(
			$s->before(fn (): string => 'string', $s->string()),
			null,
		));
	}

	public function testBeforeArrayShape(): void
	{
		$s = TypeSchema::get();

		self::assertSame('string', $this->validStrictParse(
			$s->before(fn (): string => 'string', $s->string()),
			null,
		));
	}

}
