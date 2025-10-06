<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
assertType('Shredio\TypeSchema\Types\Type<mixed>', $s->mixed());
assertType('Shredio\TypeSchema\Types\Type<mixed>', $s->nullable($s->mixed()));
assertType('mixed', TypeSchemaProcessor::createDefault()->process('hello', $s->mixed()));
assertType('mixed', TypeSchemaProcessor::createDefault()->process(123, $s->mixed()));
assertType('mixed', TypeSchemaProcessor::createDefault()->process(null, $s->mixed()));
assertType('mixed', TypeSchemaProcessor::createDefault()->process(['array'], $s->mixed()));
assertType('mixed', TypeSchemaProcessor::createDefault()->process('hello', $s->nullable($s->mixed())));

// after
assertType('Shredio\TypeSchema\Types\Type<string>', $s->mixed()->after(fn (mixed $v): string => (string) $v));
assertType('string', TypeSchemaProcessor::createDefault()->process(123, $s->mixed()->after(fn (mixed $v): string => (string) $v)));
