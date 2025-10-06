<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();

// Basic union types
assertType('Shredio\TypeSchema\Types\Type<int|string>', $s->union([$s->int(), $s->string()]));
assertType('Shredio\TypeSchema\Types\Type<bool|string>', $s->union([$s->string(), $s->bool()]));
assertType('Shredio\TypeSchema\Types\Type<bool|int>', $s->union([$s->int(), $s->bool()]));

// Parse results
assertType('int|string', TypeSchemaProcessor::createDefault()->process(123, $s->union([$s->int(), $s->string()])));
assertType('int|string', TypeSchemaProcessor::createDefault()->process('hello', $s->union([$s->int(), $s->string()])));
assertType('bool|string', TypeSchemaProcessor::createDefault()->process('test', $s->union([$s->string(), $s->bool()])));
assertType('bool|string', TypeSchemaProcessor::createDefault()->process(true, $s->union([$s->string(), $s->bool()])));

// Three-way unions
assertType('Shredio\TypeSchema\Types\Type<bool|int|string>', $s->union([$s->int(), $s->string(), $s->bool()]));
assertType('bool|int|string', TypeSchemaProcessor::createDefault()->process(42, $s->union([$s->int(), $s->string(), $s->bool()])));
assertType('bool|int|string', TypeSchemaProcessor::createDefault()->process('hello', $s->union([$s->int(), $s->string(), $s->bool()])));
assertType('bool|int|string', TypeSchemaProcessor::createDefault()->process(false, $s->union([$s->int(), $s->string(), $s->bool()])));

// Unions with nullable types
assertType('Shredio\TypeSchema\Types\Type<int|string|null>', $s->union([$s->int(), $s->nullable($s->string())]));
assertType('int|string|null', TypeSchemaProcessor::createDefault()->process(123, $s->union([$s->int(), $s->nullable($s->string())])));
assertType('int|string|null', TypeSchemaProcessor::createDefault()->process('hello', $s->union([$s->int(), $s->nullable($s->string())])));
assertType('int|string|null', TypeSchemaProcessor::createDefault()->process(null, $s->union([$s->int(), $s->nullable($s->string())])));

// Union with non-empty string
assertType('Shredio\TypeSchema\Types\Type<int|non-empty-string>', $s->union([$s->int(), $s->nonEmptyString()]));
assertType('int|non-empty-string', TypeSchemaProcessor::createDefault()->process(123, $s->union([$s->int(), $s->nonEmptyString()])));
assertType('int|non-empty-string', TypeSchemaProcessor::createDefault()->process('hello', $s->union([$s->int(), $s->nonEmptyString()])));

// Single type in union (should return the type itself)
assertType('Shredio\TypeSchema\Types\Type<int>', $s->union([$s->int()]));
assertType('int', TypeSchemaProcessor::createDefault()->process(123, $s->union([$s->int()])));

// Complex nested scenarios
assertType('Shredio\TypeSchema\Types\Type<bool|int|string|null>', $s->union([$s->union([$s->int(), $s->string()]), $s->union([$s->bool(), $s->nullable($s->string())])]));

// After transformation with unions
assertType('Shredio\TypeSchema\Types\Type<string>', $s->union([$s->int(), $s->string()])->after(fn (int|string $v): string => (string) $v));
assertType('string', TypeSchemaProcessor::createDefault()->process(123, $s->union([$s->int(), $s->string()])->after(fn (int|string $v): string => (string) $v)));
assertType('string', TypeSchemaProcessor::createDefault()->process('hello', $s->union([$s->int(), $s->string()])->after(fn (int|string $v): string => (string) $v)));
