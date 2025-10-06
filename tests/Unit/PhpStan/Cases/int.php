<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
assertType('Shredio\TypeSchema\Types\Type<int>', $s->int());
assertType('Shredio\TypeSchema\Types\Type<int|null>', $s->nullable($s->int()));
assertType('int', TypeSchemaProcessor::createDefault()->process(true, $s->int()));
assertType('int|null', TypeSchemaProcessor::createDefault()->process(true, $s->nullable($s->int())));

// after
assertType('Shredio\TypeSchema\Types\Type<lowercase-string&numeric-string&uppercase-string>', $s->int()->after(fn (int $v): string => (string) $v));
assertType('lowercase-string&numeric-string&uppercase-string', TypeSchemaProcessor::createDefault()->process(123, $s->int()->after(fn (int $v): string => (string) $v)));
