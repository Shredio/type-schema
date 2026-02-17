# shredio/type-schema

PHP runtime type validation and parsing library with PHPStan integration. Requires PHP 8.3+.

## Setup

```php
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;

$schema = TypeSchema::get(); // singleton
$processor = TypeSchemaProcessor::createDefault();
```

## Defining Types

All types are created via `TypeSchema::get()`:

```php
$s = TypeSchema::get();

// Scalars
$s->int()              // Type<int>
$s->string()           // Type<string>
$s->nonEmptyString()   // Type<non-empty-string>
$s->bool()             // Type<bool>
$s->float()            // Type<float>
$s->null()             // Type<null>
$s->mixed()            // Type<mixed>
$s->intRange(1, 100)   // Type<int> with min/max
$s->arrayKey()         // Type<array-key>

// Nullable
$s->nullable($s->string())  // Type<string|null>

// Lists & arrays
$s->list($s->int())                       // Type<list<int>>
$s->nonEmptyList($s->string())            // Type<non-empty-list<string>>
$s->array($s->string(), $s->int())        // Type<array<string, int>>

// Array shapes (structured objects)
$s->arrayShape([
    'name' => $s->string(),
    'age'  => $s->int(),
    'bio'  => $s->optional($s->string()), // optional field
])

// Union types
$s->union([$s->string(), $s->int()])  // Type<string|int>

// Objects & enums (via class mappers)
$s->mapper(Status::class)   // Type<Status> - works with BackedEnum, DateTime
$s->object(Foo::class)      // Type<Foo> - validates instanceof
```

## Validating Data

`TypeSchemaProcessor` provides three validation methods:

```php
// Returns T|ErrorElement - no exceptions
$result = $processor->parse($data, $type);
if ($result instanceof ErrorElement) { /* handle error */ }

// Returns T, throws AssertException on failure (collects all errors)
$result = $processor->process($data, $type);

// Returns T, throws AssertException on failure (stops at first error, faster)
$result = $processor->processFast($data, $type);

// Returns bool
$isValid = $processor->matches($data, $type);
```

## Conversion Strategies

Control how input values are coerced to target types:

```php
use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;

// Available strategies:
ConversionStrategyFactory::strict()   // no coercion (default)
ConversionStrategyFactory::lenient()  // "123" -> 123, etc.
ConversionStrategyFactory::json()     // for decoded JSON
ConversionStrategyFactory::csv()      // for CSV data
ConversionStrategyFactory::httpGet()  // alias for csv()
ConversionStrategyFactory::database() // for database rows

// Pass via TypeConfig:
$config = new TypeConfig(
    conversionStrategy: ConversionStrategyFactory::json(),
);
$result = $processor->process($data, $type, $config);

// Or set as default:
$processor = TypeSchemaProcessor::createDefault(
    conversionStrategy: ConversionStrategyFactory::json(),
);
```

## Transformations and Custom Validation

Chain `after()` and `validate()` on any type:

```php
// Transform after successful parse
$s->string()->after(fn(string $v): string => trim($v))

// Custom validation - return ErrorElement on failure, null on success
$s->string()->validate(function (string $v, TypeContext $ctx): ?ErrorElement {
    return strlen($v) < 3
        ? $ctx->errorElementFactory->createError('Too short')
        : null;
})

// Pre-process before type validation
$s->before(
    fn(mixed $v, TypeContext $ctx): mixed => is_string($v) ? json_decode($v, true) : $v,
    $s->arrayShape([...]),
)
```

## Extra Keys in Array Shapes

```php
use Shredio\TypeSchema\Enum\ExtraKeysBehavior;

// Reject extra keys (default when not configured)
$s->arrayShape([...], ExtraKeysBehavior::Reject)

// Accept and keep extra keys
$s->arrayShape([...], ExtraKeysBehavior::Accept)

// Silently strip extra keys
$s->arrayShape([...], ExtraKeysBehavior::Ignore)
```

## Error Handling

```php
use Shredio\TypeSchema\Exception\AssertException;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\TypeSchemaErrorFormatter;

// With process() / processFast():
try {
    $result = $processor->process($data, $type);
} catch (AssertException $e) {
    echo $e->toPrettyString();

    foreach ($e->getErrors() as $error) {
        $error->message;              // user-facing
        $error->messageForDeveloper;  // developer-facing
        $error->toDebugPathString();  // e.g. "address.city"
    }
}

// With parse():
$result = $processor->parse($data, $type);
if ($result instanceof ErrorElement) {
    echo TypeSchemaErrorFormatter::prettyString($result);
}
```

## Complete Example

```php
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Exception\AssertException;

$s = TypeSchema::get();
$processor = TypeSchemaProcessor::createDefault(
    conversionStrategy: ConversionStrategyFactory::json(),
);

$userSchema = $s->arrayShape([
    'id'      => $s->int(),
    'name'    => $s->nonEmptyString(),
    'email'   => $s->string(),
    'age'     => $s->nullable($s->intRange(0, 150)),
    'roles'   => $s->list($s->nonEmptyString()),
    'address' => $s->optional($s->arrayShape([
        'street' => $s->string(),
        'city'   => $s->string(),
    ])),
    'status'  => $s->mapper(UserStatus::class), // BackedEnum
]);

try {
    $user = $processor->process($jsonData, $userSchema);
    // $user is array{id: int, name: non-empty-string, email: string, ...}
} catch (AssertException $e) {
    echo $e->toPrettyString();
}
```

## Key Design Points

- All types are immutable `readonly` classes
- `parse()` returns `T|ErrorElement` (no exceptions)
- `process()` throws `AssertException` with collected errors
- PHPStan infers return types from schema definitions
- Types are composable: `nullable(list(arrayShape([...])))`
- Custom mappers handle BackedEnum and DateTime out of the box