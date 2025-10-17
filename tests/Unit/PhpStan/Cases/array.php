<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();

// array<string, int>
$schema = $s->array($s->string(), $s->int());
assertType('Shredio\TypeSchema\Types\Type<array<string, int>>', $schema);
assertType('array<string, int>', TypeSchemaProcessor::createDefault()->process([], $schema));

// array<int, string>
$schema = $s->array($s->int(), $s->string());
assertType('Shredio\TypeSchema\Types\Type<array<int, string>>', $schema);
assertType('array<int, string>', TypeSchemaProcessor::createDefault()->process([], $schema));

// array<string, string>
$schema = $s->array($s->string(), $s->string());
assertType('Shredio\TypeSchema\Types\Type<array<string, string>>', $schema);
assertType('array<string, string>', TypeSchemaProcessor::createDefault()->process([], $schema));

// array<int, int>
$schema = $s->array($s->int(), $s->int());
assertType('Shredio\TypeSchema\Types\Type<array<int, int>>', $schema);
assertType('array<int, int>', TypeSchemaProcessor::createDefault()->process([], $schema));

// nested array<string, array<int, string>>
$schema = $s->array($s->string(), $s->array($s->int(), $s->string()));
assertType('Shredio\TypeSchema\Types\Type<array<string, array<int, string>>>', $schema);
assertType('array<string, array<int, string>>', TypeSchemaProcessor::createDefault()->process([], $schema));

// array with arrayShape as value
$schema = $s->array($s->string(), $s->arrayShape([
	'id' => $s->int(),
	'name' => $s->string(),
]));
assertType('Shredio\TypeSchema\Types\Type<array<string, array{id: int, name: string}>>', $schema);
assertType('array<string, array{id: int, name: string}>', TypeSchemaProcessor::createDefault()->process([], $schema));

// array with list as value
$schema = $s->array($s->string(), $s->list($s->int()));
assertType('Shredio\TypeSchema\Types\Type<array<string, list<int>>>', $schema);
assertType('array<string, list<int>>', TypeSchemaProcessor::createDefault()->process([], $schema));

// array with non-empty-list as value
$schema = $s->array($s->string(), $s->nonEmptyList($s->int()));
assertType('Shredio\TypeSchema\Types\Type<array<string, non-empty-list<int>>>', $schema);
assertType('array<string, non-empty-list<int>>', TypeSchemaProcessor::createDefault()->process([], $schema));

// nullable array
$schema = $s->nullable($s->array($s->string(), $s->int()));
assertType('Shredio\TypeSchema\Types\Type<array<string, int>|null>', $schema);
assertType('array<string, int>|null', TypeSchemaProcessor::createDefault()->process([], $schema));

// array with nullable values
$schema = $s->array($s->string(), $s->nullable($s->int()));
assertType('Shredio\TypeSchema\Types\Type<array<string, int|null>>', $schema);
assertType('array<string, int|null>', TypeSchemaProcessor::createDefault()->process([], $schema));

// array with array-key
$schema = $s->array($s->arrayKey(), $s->string());
assertType('Shredio\TypeSchema\Types\Type<array<string>>', $schema);
assertType('array<string>', TypeSchemaProcessor::createDefault()->process([], $schema));

// complex nested structure
$schema = $s->array(
	$s->string(),
	$s->arrayShape([
		'users' => $s->list($s->arrayShape([
			'id' => $s->int(),
			'name' => $s->nonEmptyString(),
			'tags' => $s->array($s->string(), $s->int()),
		])),
		'meta' => $s->arrayShape([
			'count' => $s->int(),
			'total' => $s->int(),
		]),
	])
);
assertType('Shredio\TypeSchema\Types\Type<array<string, array{users: list<array{id: int, name: non-empty-string, tags: array<string, int>}>, meta: array{count: int, total: int}}>>', $schema);
assertType('array<string, array{users: list<array{id: int, name: non-empty-string, tags: array<string, int>}>, meta: array{count: int, total: int}}>', TypeSchemaProcessor::createDefault()->process([], $schema));
