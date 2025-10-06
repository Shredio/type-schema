<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
assertType('Shredio\TypeSchema\Types\Type<non-empty-string>', $s->nonEmptyString());
assertType('Shredio\TypeSchema\Types\Type<non-empty-string|null>', $s->nullable($s->nonEmptyString()));
assertType('non-empty-string', TypeSchemaProcessor::createDefault()->process('hello', $s->nonEmptyString()));
assertType('non-empty-string|null', TypeSchemaProcessor::createDefault()->process('hello', $s->nullable($s->nonEmptyString())));

// after
assertType('Shredio\TypeSchema\Types\Type<int>', $s->nonEmptyString()->after(fn (string $v): int => (int) $v));
assertType('int', TypeSchemaProcessor::createDefault()->process('123', $s->nonEmptyString()->after(fn (string $v): int => (int) $v)));
