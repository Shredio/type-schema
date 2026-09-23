<?php declare(strict_types = 1);

use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
$processor = TypeSchemaProcessor::createDefault();

$schema = $s->arrayShape([
	'id' => $s->int(),
	'name' => $s->nonEmptyString(),
	'optional' => $s->optional($s->intRange(1, 10)),
]);

// constant arrays keep their precision (a plain template signature would generalize non-empty-string to string)
$result = $processor->parse([], $schema);
assertType('Shredio\TypeSchema\Result\Failure|Shredio\TypeSchema\Result\Success<array{id: int, name: non-empty-string, optional?: int<1, 10>}>', $result);

if (!$result instanceof Failure) {
	assertType('array{id: int, name: non-empty-string, optional?: int<1, 10>}', $result->value);
}

// named arguments
assertType('Shredio\TypeSchema\Result\Failure|Shredio\TypeSchema\Result\Success<non-empty-string>', $processor->parse(type: $s->nonEmptyString(), value: 'x'));

// promotion of notices keeps the value type
assertType('Shredio\TypeSchema\Result\Failure|Shredio\TypeSchema\Result\Success<array{id: int, name: non-empty-string, optional?: int<1, 10>}>', $processor->parse([], $schema)->withNoticesAsErrors());

// process() is unchanged
assertType('array{id: int, name: non-empty-string, optional?: int<1, 10>}', $processor->process([], $schema));
