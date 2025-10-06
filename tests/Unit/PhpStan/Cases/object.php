<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
assertType('Shredio\TypeSchema\Types\Type<object>', $s->object());
assertType('Shredio\TypeSchema\Types\Type<DateTime>', $s->object(DateTime::class));
assertType('Shredio\TypeSchema\Types\Type<Iterator>', $s->object(Iterator::class));
assertType('Shredio\TypeSchema\Types\Type<object|null>', $s->nullable($s->object()));

assertType('object', TypeSchemaProcessor::createDefault()->process(new stdClass(), $s->object()));
assertType('DateTime', TypeSchemaProcessor::createDefault()->process(new DateTime(), $s->object(DateTime::class)));
assertType('Iterator', TypeSchemaProcessor::createDefault()->process(new ArrayIterator([]), $s->object(Iterator::class)));
assertType('object|null', TypeSchemaProcessor::createDefault()->process(new stdClass(), $s->nullable($s->object())));
assertType('object|null', TypeSchemaProcessor::createDefault()->process(null, $s->nullable($s->object())));

// after
assertType('Shredio\TypeSchema\Types\Type<class-string&literal-string>', $s->object()->after(fn (object $v): string => $v::class));
assertType('class-string&literal-string', TypeSchemaProcessor::createDefault()->process(new stdClass(), $s->object()->after(fn (object $v): string => $v::class)));
assertType('Shredio\TypeSchema\Types\Type<non-falsy-string>', $s->object(DateTime::class)->after(fn (DateTime $v): string => $v->format('Y-m-d')));
assertType('non-falsy-string', TypeSchemaProcessor::createDefault()->process(new DateTime(), $s->object(DateTime::class)->after(fn (DateTime $v): string => $v->format('Y-m-d'))));
