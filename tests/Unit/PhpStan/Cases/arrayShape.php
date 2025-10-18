<?php declare(strict_types = 1);

use Shredio\TypeSchema\Helper\TypeSchemaHelper;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();

$schema = $s->arrayShape([
	'int' => $s->int(),
	'bool' => $s->bool(),
]);
assertType('Shredio\TypeSchema\Types\Type<array{int: int, bool: bool}>', $schema);
assertType('array{int: int, bool: bool}', TypeSchemaProcessor::createDefault()->process([], $schema));

//// nested
$schema = $s->arrayShape([
	'inner' => $s->arrayShape([
		'str' => $s->nonEmptyString(),
		'inner2' => $s->arrayShape([
			'str' => $s->nonEmptyString(),
		]),
	]),
	'list' => $s->nonEmptyList($s->arrayShape([
		'str' => $s->nonEmptyString(),
		'int' => $s->int(),
		'inner2' => $s->arrayShape([
			'str' => $s->nonEmptyString(),
			'int' => $s->int(),
			'inner3' => $s->arrayShape([
				'str' => $s->nonEmptyString(),
				'int' => $s->int(),
			]),
		]),
	])),
]);

assertType('Shredio\TypeSchema\Types\Type<array{inner: array{str: non-empty-string, inner2: array{str: non-empty-string}}, list: non-empty-list<array{str: non-empty-string, int: int, inner2: array{str: non-empty-string, int: int, inner3: array{str: non-empty-string, int: int}}}>}>', $schema);
assertType('array{inner: array{str: non-empty-string, inner2: array{str: non-empty-string}}, list: non-empty-list<array{str: non-empty-string, int: int, inner2: array{str: non-empty-string, int: int, inner3: array{str: non-empty-string, int: int}}}>}', TypeSchemaProcessor::createDefault()->process([], $schema));

// optional
$schema = $s->arrayShape([
	'int' => $s->int(),
	'bool' => $s->bool(),
	'optional' => $s->optional($s->int()),
]);

assertType('Shredio\TypeSchema\Types\Type<array{int: int, bool: bool, optional?: int}>', $schema);

// nullable
$schema = $s->nullable($s->arrayShape([
	'int' => $s->int(),
	'bool' => $s->bool(),
	'nullable' => $s->nullable($s->int()),
]));

assertType('Shredio\TypeSchema\Types\Type<array{int: int, bool: bool, nullable: int|null}|null>', $schema);
assertType('array{int: int, bool: bool, nullable: int|null}|null', TypeSchemaProcessor::createDefault()->process([], $schema));

assertType('array{int: int, bool: bool, nullable: int|null}|null', TypeSchemaProcessor::createDefault()->process([], TypeSchemaHelper::reindexShape([], $schema)));
