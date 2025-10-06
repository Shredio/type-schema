<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
assertType('Shredio\TypeSchema\Types\Type<bool>', $s->bool());
assertType('bool', TypeSchemaProcessor::createDefault()->process(true, $s->bool()));

// nullable
assertType('Shredio\TypeSchema\Types\Type<bool|null>', $s->nullable($s->bool()));
assertType('bool|null', TypeSchemaProcessor::createDefault()->process(true, $s->nullable($s->bool())));
