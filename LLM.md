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

`TypeSchemaProcessor` provides these validation methods:

```php
use Shredio\TypeSchema\Result\Failure;

// Returns Success<T>|Failure - no exceptions, notices (e.g. extra keys) do not make it fail
$result = $processor->parse($data, $type);
if ($result instanceof Failure) { /* handle errors */ }
$result->value;            // T
$result->hasNotices();     // non-critical problems, e.g. removed extra keys
$result->getNoticeReports();

// Returns T, throws AssertException on failure (collects all errors, notices are errors)
$result = $processor->process($data, $type);

// Returns T, throws AssertException on failure (stops at first error, faster, notices are errors)
$result = $processor->processFast($data, $type);

// Returns bool - true only without errors and without notices
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

// Custom validation - return an issue on failure, null on success
$s->string()->validate(function (string $v): ?IssueNode {
    return strlen($v) < 3
        ? new CustomIssue('Too short')
        : null;
})

// Pre-process before type validation
$s->before(
    fn(mixed $v, TypeContext $ctx): mixed => is_string($v) ? json_decode($v, true) : $v,
    $s->arrayShape([...]),
)
```

## Extra Keys in Array Shapes

Keys not defined in a closed shape are removed from the result and reported as `ExtraKey` notices.
The caller decides what to do with them:

```php
$result = $processor->parse($data, $s->arrayShape([...]));

// strict: treat notices as errors (process(), processFast() and matches() do this)
$result = $result->withNoticesAsErrors();

// lenient: log notices and continue
foreach ($result->getNoticeReports() as $notice) {
    $logger->notice($notice->messageForDeveloper, ['path' => $notice->toDebugPathString()]);
}

// open shape: extra keys are parsed with the rest type and kept
$s->arrayShape([...], rest: $s->mixed())
$s->arrayShape([...], rest: $s->string())
```

## Error Handling

Errors are data (`Shredio\TypeSchema\Issue\*`: `InvalidType`, `MissingKey`, `ExtraKey`, `NumberOutOfRange`, ...).
User-facing messages are produced after parsing by an `IssueRenderer` (`Issue\Renderer\EnglishIssueRenderer`,
`Issue\Renderer\SymfonyIssueRenderer`) into `Issue\Report\ErrorReport` objects.
Each issue has an `ErrorCategory`: `Structural` (typically HTTP 400) or `Validation` (typically HTTP 422).

```php
use Shredio\TypeSchema\Exception\AssertException;
use Shredio\TypeSchema\Issue\ErrorCategory;
use Shredio\TypeSchema\Issue\Report\TypeSchemaErrorFormatter;
use Shredio\TypeSchema\Result\Failure;

// With process() / processFast():
try {
    $result = $processor->process($data, $type);
} catch (AssertException $e) {
    echo $e->toPrettyString();

    foreach ($e->getErrors() as $error) {
        $error->message;              // user-facing, rendered by the processor's renderer
        $error->messageForDeveloper;  // developer-facing
        $error->toDebugPathString();  // e.g. "address.city"
        $error->issue;                // raw issue data
    }
}

// With parse():
$result = $processor->parse($data, $type);
if ($result instanceof Failure) {
    $status = $result->getCategory() === ErrorCategory::Structural ? 400 : 422;
    $reports = $result->getReports($processor->getIssueRenderer());
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
- `parse()` returns `Success<T>|Failure` (no exceptions), notices are left to the caller
- `process()` throws `AssertException` with collected errors, notices are treated as errors
- PHPStan infers return types from schema definitions
- Types are composable: `nullable(list(arrayShape([...])))`
- Custom mappers handle BackedEnum and DateTime out of the box