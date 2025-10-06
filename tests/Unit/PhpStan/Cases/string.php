<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
assertType('Shredio\TypeSchema\Types\Type<string>', $s->string());
assertType('Shredio\TypeSchema\Types\Type<string|null>', $s->nullable($s->string()));
assertType('string', TypeSchemaProcessor::createDefault()->process('hello', $s->string()));
assertType('string|null', TypeSchemaProcessor::createDefault()->process('hello', $s->nullable($s->string())));

// after
assertType('Shredio\TypeSchema\Types\Type<int>', $s->string()->after(fn (string $v): int => (int) $v));
assertType('int', TypeSchemaProcessor::createDefault()->process('123', $s->string()->after(fn (string $v): int => (int) $v)));
