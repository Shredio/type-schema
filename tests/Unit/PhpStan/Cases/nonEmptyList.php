<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();

// basic nonEmptyList
assertType('Shredio\TypeSchema\Types\Type<non-empty-list<int>>', $s->nonEmptyList($s->int()));
assertType('Shredio\TypeSchema\Types\Type<non-empty-list<string>>', $s->nonEmptyList($s->string()));
assertType('Shredio\TypeSchema\Types\Type<non-empty-list<bool>>', $s->nonEmptyList($s->bool()));

// parsing
assertType('non-empty-list<int>', TypeSchemaProcessor::createDefault()->process([1, 2, 3], $s->nonEmptyList($s->int())));
assertType('non-empty-list<string>', TypeSchemaProcessor::createDefault()->process(['a', 'b', 'c'], $s->nonEmptyList($s->string())));
assertType('non-empty-list<bool>', TypeSchemaProcessor::createDefault()->process([true, false], $s->nonEmptyList($s->bool())));

// nullable nonEmptyList
assertType('Shredio\TypeSchema\Types\Type<non-empty-list<int>|null>', $s->nullable($s->nonEmptyList($s->int())));
assertType('non-empty-list<int>|null', TypeSchemaProcessor::createDefault()->process([1, 2, 3], $s->nullable($s->nonEmptyList($s->int()))));
assertType('non-empty-list<int>|null', TypeSchemaProcessor::createDefault()->process(null, $s->nullable($s->nonEmptyList($s->int()))));

// nonEmptyList of nullable items
assertType('Shredio\TypeSchema\Types\Type<non-empty-list<int|null>>', $s->nonEmptyList($s->nullable($s->int())));
assertType('non-empty-list<int|null>', TypeSchemaProcessor::createDefault()->process([1, null, 3], $s->nonEmptyList($s->nullable($s->int()))));

// nested nonEmptyList
assertType('Shredio\TypeSchema\Types\Type<non-empty-list<non-empty-list<string>>>', $s->nonEmptyList($s->nonEmptyList($s->string())));
assertType('non-empty-list<non-empty-list<string>>', TypeSchemaProcessor::createDefault()->process([['a', 'b'], ['c', 'd']], $s->nonEmptyList($s->nonEmptyList($s->string()))));

// nonEmptyList of complex types
assertType('Shredio\TypeSchema\Types\Type<non-empty-list<non-empty-string>>', $s->nonEmptyList($s->nonEmptyString()));
assertType('non-empty-list<non-empty-string>', TypeSchemaProcessor::createDefault()->process(['hello', 'world'], $s->nonEmptyList($s->nonEmptyString())));

// nonEmptyList of array shapes
$shapeType = $s->arrayShape([
	'id' => $s->int(),
	'name' => $s->nonEmptyString(),
]);
assertType('Shredio\TypeSchema\Types\Type<non-empty-list<array{id: int, name: non-empty-string}>>', $s->nonEmptyList($shapeType));
assertType('non-empty-list<array{id: int, name: non-empty-string}>', TypeSchemaProcessor::createDefault()->process([['id' => 1, 'name' => 'test']], $s->nonEmptyList($shapeType)));

// after
assertType('Shredio\TypeSchema\Types\Type<non-empty-list<int>>', $s->nonEmptyList($s->int())->after(fn (array $v): array => array_map(fn (int $x): int => $x * 2, $v)));
assertType('non-empty-list<int>', TypeSchemaProcessor::createDefault()->process([1, 2, 3], $s->nonEmptyList($s->int())->after(fn (array $v): array => array_map(fn (int $x): int => $x * 2, $v))));

assertType('Shredio\TypeSchema\Types\Type<lowercase-string&non-falsy-string&uppercase-string>', $s->nonEmptyList($s->int())->after(fn (array $v): string => implode(',', $v)));
assertType('lowercase-string&non-falsy-string&uppercase-string', TypeSchemaProcessor::createDefault()->process([1, 2, 3], $s->nonEmptyList($s->int())->after(fn (array $v): string => implode(',', $v))));
