<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
assertType('Shredio\TypeSchema\Types\Type<int|null>', $s->nullable($s->int()));
assertType('Shredio\TypeSchema\Types\Type<string|null>', $s->nullable($s->string()));
assertType('Shredio\TypeSchema\Types\Type<non-empty-string|null>', $s->nullable($s->nonEmptyString()));
assertType('Shredio\TypeSchema\Types\Type<bool|null>', $s->nullable($s->bool()));

assertType('int|null', TypeSchemaProcessor::createDefault()->process(123, $s->nullable($s->int())));
assertType('int|null', TypeSchemaProcessor::createDefault()->process(null, $s->nullable($s->int())));
assertType('string|null', TypeSchemaProcessor::createDefault()->process('hello', $s->nullable($s->string())));
assertType('string|null', TypeSchemaProcessor::createDefault()->process(null, $s->nullable($s->string())));

// nested nullable
assertType('Shredio\TypeSchema\Types\Type<int|null>', $s->nullable($s->nullable($s->int())));
assertType('int|null', TypeSchemaProcessor::createDefault()->process(123, $s->nullable($s->nullable($s->int()))));
assertType('int|null', TypeSchemaProcessor::createDefault()->process(null, $s->nullable($s->nullable($s->int()))));

// after
assertType('Shredio\TypeSchema\Types\Type<(lowercase-string&numeric-string&uppercase-string)|null>', $s->nullable($s->int())->after(fn (?int $v): ?string => $v === null ? null : (string) $v));
assertType('(lowercase-string&numeric-string&uppercase-string)|null', TypeSchemaProcessor::createDefault()->process(123, $s->nullable($s->int())->after(fn (?int $v): ?string => $v === null ? null : (string) $v)));
