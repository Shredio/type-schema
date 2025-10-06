<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();

// Basic transformation: int to string
assertType('Shredio\TypeSchema\Types\Type<lowercase-string&numeric-string&uppercase-string>', $s->int()->after(fn (int $v): string => (string) $v));
assertType('lowercase-string&numeric-string&uppercase-string', TypeSchemaProcessor::createDefault()->process(123, $s->int()->after(fn (int $v): string => (string) $v)));

// Transformation: int to int (calculation)
assertType('Shredio\TypeSchema\Types\Type<int>', $s->int()->after(fn (int $v): int => $v * 2));
assertType('int', TypeSchemaProcessor::createDefault()->process(5, $s->int()->after(fn (int $v): int => $v * 2)));

// Transformation: int to bool
assertType('Shredio\TypeSchema\Types\Type<bool>', $s->int()->after(fn (int $v): bool => $v > 0));
assertType('bool', TypeSchemaProcessor::createDefault()->process(42, $s->int()->after(fn (int $v): bool => $v > 0)));

// Transformation: int to array
assertType('Shredio\TypeSchema\Types\Type<array{value: int, doubled: int}>', $s->int()->after(fn (int $v): array => ['value' => $v, 'doubled' => $v * 2]));
assertType('array{value: int, doubled: int}', TypeSchemaProcessor::createDefault()->process(7, $s->int()->after(fn (int $v): array => ['value' => $v, 'doubled' => $v * 2])));

// Transformation: bool to string
assertType('Shredio\TypeSchema\Types\Type<string>', $s->bool()->after(fn (bool $v): string => $v ? 'true' : 'false'));
assertType('string', TypeSchemaProcessor::createDefault()->process(true, $s->bool()->after(fn (bool $v): string => $v ? 'true' : 'false')));

// Transformation: bool to int
assertType('Shredio\TypeSchema\Types\Type<int>', $s->bool()->after(fn (bool $v): int => $v ? 1 : 0));
assertType('int', TypeSchemaProcessor::createDefault()->process(false, $s->bool()->after(fn (bool $v): int => $v ? 1 : 0)));

// Chaining transformations
assertType('Shredio\TypeSchema\Types\Type<non-falsy-string>', $s->int()->after(fn (int $v): int => $v * 2)->after(fn (int $v): string => "Result: $v"));
assertType('non-falsy-string', TypeSchemaProcessor::createDefault()->process(3, $s->int()->after(fn (int $v): int => $v * 2)->after(fn (int $v): string => "Result: $v")));

// With nullable types
assertType('Shredio\TypeSchema\Types\Type<(lowercase-string&numeric-string&uppercase-string)|null>', $s->nullable($s->int())->after(fn (?int $v): ?string => $v !== null ? (string) $v : null));
assertType('(lowercase-string&numeric-string&uppercase-string)|null', TypeSchemaProcessor::createDefault()->process(null, $s->nullable($s->int())->after(fn (?int $v): ?string => $v !== null ? (string) $v : null)));

// Complex transformation with union types
assertType('Shredio\TypeSchema\Types\Type<string>', $s->int()->after(function (int $v): string {
    if ($v > 0) return 'positive';
    if ($v < 0) return 'negative';
    return 'zero';
}));

// String type transformations
assertType('Shredio\TypeSchema\Types\Type<int<1, max>>', $s->nonEmptyString()->after(fn (string $v): int => strlen($v)));
assertType('int<1, max>', TypeSchemaProcessor::createDefault()->process('hello', $s->nonEmptyString()->after(fn (string $v): int => strlen($v))));

// Array shape transformations
assertType('Shredio\TypeSchema\Types\Type<non-falsy-string>', $s->arrayShape(['name' => $s->nonEmptyString()])->after(fn (array $v): string => "Hello, {$v['name']}!"));
