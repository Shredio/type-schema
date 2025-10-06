<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
assertType('Shredio\TypeSchema\Types\Type<float>', $s->float());
assertType('Shredio\TypeSchema\Types\Type<float|null>', $s->nullable($s->float()));
assertType('float', TypeSchemaProcessor::createDefault()->process(3.14, $s->float()));
assertType('float|null', TypeSchemaProcessor::createDefault()->process(3.14, $s->nullable($s->float())));

// after
assertType('Shredio\TypeSchema\Types\Type<int>', $s->float()->after(fn (float $v): int => (int) $v));
assertType('int', TypeSchemaProcessor::createDefault()->process(3.14, $s->float()->after(fn (float $v): int => (int) $v)));
