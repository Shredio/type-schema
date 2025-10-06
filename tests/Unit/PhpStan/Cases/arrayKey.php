<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
assertType('Shredio\TypeSchema\Types\Type<(int|string)>', $s->arrayKey());
assertType('Shredio\TypeSchema\Types\Type<int|string|null>', $s->nullable($s->arrayKey()));

assertType('(int|string)', TypeSchemaProcessor::createDefault()->process('hello', $s->arrayKey()));
assertType('(int|string)', TypeSchemaProcessor::createDefault()->process(123, $s->arrayKey()));
assertType('(int|string)', TypeSchemaProcessor::createDefault()->process('', $s->arrayKey()));
assertType('(int|string)', TypeSchemaProcessor::createDefault()->process(0, $s->arrayKey()));
assertType('int|string|null', TypeSchemaProcessor::createDefault()->process('hello', $s->nullable($s->arrayKey())));
assertType('int|string|null', TypeSchemaProcessor::createDefault()->process(null, $s->nullable($s->arrayKey())));

// after
assertType('Shredio\TypeSchema\Types\Type<int<0, max>>', $s->arrayKey()->after(fn (int|string $v): int => is_string($v) ? strlen($v) : abs($v) + 1));
assertType('int<0, max>', TypeSchemaProcessor::createDefault()->process('hello', $s->arrayKey()->after(fn (int|string $v): int => is_string($v) ? strlen($v) : abs($v) + 1)));
assertType('int<0, max>', TypeSchemaProcessor::createDefault()->process(123, $s->arrayKey()->after(fn (int|string $v): int => is_string($v) ? strlen($v) : abs($v) + 1)));
