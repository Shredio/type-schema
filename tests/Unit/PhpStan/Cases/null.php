<?php declare(strict_types = 1);

use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use function PHPStan\Testing\assertType;

$s = TypeSchema::get();
assertType('Shredio\TypeSchema\Types\Type<null>', $s->null());
assertType('null', TypeSchemaProcessor::createDefault()->process(null, $s->null()));

// nullable null type (should remain null)
assertType('Shredio\TypeSchema\Types\Type<null>', $s->nullable($s->null()));
assertType('null', TypeSchemaProcessor::createDefault()->process(null, $s->nullable($s->null())));
