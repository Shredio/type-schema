<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();

// basic list
assertType('Shredio\TypeSchema\Types\Type<list<int>>', $s->list($s->int()));
assertType('Shredio\TypeSchema\Types\Type<list<string>>', $s->list($s->string()));
assertType('Shredio\TypeSchema\Types\Type<list<bool>>', $s->list($s->bool()));
assertType('Shredio\TypeSchema\Types\Type<list<float>>', $s->list($s->float()));

// parsing
assertType('list<int>', TypeSchemaProcessor::createDefault()->process([1, 2, 3], $s->list($s->int())));
assertType('list<string>', TypeSchemaProcessor::createDefault()->process(['a', 'b', 'c'], $s->list($s->string())));
assertType('list<bool>', TypeSchemaProcessor::createDefault()->process([true, false], $s->list($s->bool())));
assertType('list<float>', TypeSchemaProcessor::createDefault()->process([1.1, 2.2], $s->list($s->float())));

// empty list
assertType('list<int>', TypeSchemaProcessor::createDefault()->process([], $s->list($s->int())));

// nullable list
assertType('Shredio\TypeSchema\Types\Type<list<int>|null>', $s->nullable($s->list($s->int())));
assertType('list<int>|null', TypeSchemaProcessor::createDefault()->process([1, 2, 3], $s->nullable($s->list($s->int()))));
assertType('list<int>|null', TypeSchemaProcessor::createDefault()->process(null, $s->nullable($s->list($s->int()))));

// list of nullable items
assertType('Shredio\TypeSchema\Types\Type<list<int|null>>', $s->list($s->nullable($s->int())));
assertType('list<int|null>', TypeSchemaProcessor::createDefault()->process([1, null, 3], $s->list($s->nullable($s->int()))));

// nested list
assertType('Shredio\TypeSchema\Types\Type<list<list<string>>>', $s->list($s->list($s->string())));
assertType('list<list<string>>', TypeSchemaProcessor::createDefault()->process([['a', 'b'], ['c', 'd']], $s->list($s->list($s->string()))));

// list of complex types
assertType('Shredio\TypeSchema\Types\Type<list<non-empty-string>>', $s->list($s->nonEmptyString()));
assertType('list<non-empty-string>', TypeSchemaProcessor::createDefault()->process(['hello', 'world'], $s->list($s->nonEmptyString())));

// list of array shapes
$shapeType = $s->arrayShape([
	'id' => $s->int(),
	'name' => $s->nonEmptyString(),
]);
assertType('Shredio\TypeSchema\Types\Type<list<array{id: int, name: non-empty-string}>>', $s->list($shapeType));
assertType('list<array{id: int, name: non-empty-string}>', TypeSchemaProcessor::createDefault()->process([['id' => 1, 'name' => 'test']], $s->list($shapeType)));

// after
assertType('Shredio\TypeSchema\Types\Type<list<int>>', $s->list($s->int())->after(fn (array $v): array => array_map(fn (int $x): int => $x * 2, $v)));
assertType('list<int>', TypeSchemaProcessor::createDefault()->process([1, 2, 3], $s->list($s->int())->after(fn (array $v): array => array_map(fn (int $x): int => $x * 2, $v))));

assertType('Shredio\TypeSchema\Types\Type<lowercase-string&uppercase-string>', $s->list($s->int())->after(fn (array $v): string => implode(',', $v)));
assertType('lowercase-string&uppercase-string', TypeSchemaProcessor::createDefault()->process([1, 2, 3], $s->list($s->int())->after(fn (array $v): string => implode(',', $v))));
