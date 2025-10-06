<?php declare(strict_types = 1);

namespace Tests\Common;

use Shredio\TypeSchema\Types\Type;

final readonly class TypeTestCrate
{

	/**
	 * @param list<'string'|'int'|'float'|'bool'|'null'|'array'|'object'|'backedEnum'|'unitEnum'> $calledMethodsInTypeConverter
	 */
	public function __construct(
		public Type $type,
		public array $calledMethodsInTypeConverter = [],
	)
	{
	}

}
