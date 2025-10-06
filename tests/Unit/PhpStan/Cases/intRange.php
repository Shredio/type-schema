<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
$schema = $s->intRange(1, 10);
assertType('Shredio\TypeSchema\Types\Type<int<1, 10>>', $schema);
assertType('int<1, 10>', TypeSchemaProcessor::createDefault()->process(5, $schema));

$schema = $s->nullable($s->intRange(1, 10));
assertType('Shredio\TypeSchema\Types\Type<int<1, 10>|null>', $schema);
assertType('int<1, 10>|null', TypeSchemaProcessor::createDefault()->process(5, $schema));
