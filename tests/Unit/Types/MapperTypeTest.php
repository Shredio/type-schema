<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Exception\ClassMapperNotFoundException;
use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Issue\Path;
use Shredio\TypeSchema\Mapper\ClassMapperProvider;
use Shredio\TypeSchema\Mapper\RegistryClassMapperProvider;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Types\Type;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use Shredio\TypeSchema\Validation\ValidationFailed;
use stdClass;
use Tests\Unit\Mapper\Fixtures\TestBackedStringEnum;

final class MapperTypeTest extends TestCase
{

	public function testThrowsWhenNoMapperIsFound(): void
	{
		$processor = TypeSchemaProcessor::createDefault(ConversionStrategyFactory::lenient());

		$this->expectException(ClassMapperNotFoundException::class);
		$this->expectExceptionMessage('Class mapper for class "stdClass" not found.');

		$processor->parse([], TypeSchema::get()->mapper(stdClass::class));
	}

	public function testValidationFailedExceptionBecomesFailure(): void
	{
		$issues = [
			new IssuePath(new CustomIssue('First is wrong.'), new Path('first')),
			new IssuePath(new CustomIssue('Second is wrong.'), new Path('second')),
		];
		$processor = TypeSchemaProcessor::createDefault(classMapperProvider: new SingleTypeProvider(new ThrowingType($issues)));

		$result = $processor->parse([], TypeSchema::get()->mapper(stdClass::class));

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertSame(
			['first' => 'First is wrong.', 'second' => 'Second is wrong.'],
			array_combine(
				array_map(static fn ($report): ?string => $report->toDebugPathString(), $result->getReports()),
				array_map(static fn ($report): string => (string) $report->message, $result->getReports()),
			),
		);
	}

	public function testExpectedTypeIsTakenFromProvidedMapper(): void
	{
		$t = TypeSchema::get();
		$processor = TypeSchemaProcessor::createDefault(ConversionStrategyFactory::lenient());

		$result = $processor->parse([], $t->union([$t->mapper(TestBackedStringEnum::class), $t->int()]));

		$this->assertInstanceOf(Failure::class, $result);
		$this->assertSame(
			sprintf('Invalid type array, expected (%s | int).', TestBackedStringEnum::class),
			$result->getReports()[0]->messageForDeveloper,
		);
	}

	public function testExpectedTypeIsResolvedLazilyAndRequiresMapper(): void
	{
		$t = TypeSchema::get();
		$provider = new SingleTypeProvider($t->int());
		$processor = TypeSchemaProcessor::createDefault(classMapperProvider: $provider);
		$type = $t->union([$t->mapper(stdClass::class), $t->string()]);

		$this->assertSame('Invalid type array, expected (int | string).', $this->renderFirstError($processor, $type));

		$result = $processor->parse([], $type);
		$provider->type = null; // the expected type is resolved only when the failure is rendered

		$this->assertInstanceOf(Failure::class, $result);
		$this->expectException(ClassMapperNotFoundException::class);
		$result->getReports();
	}

	/**
	 * @param Type<mixed> $type
	 */
	private function renderFirstError(TypeSchemaProcessor $processor, Type $type): string
	{
		$result = $processor->parse([], $type);
		$this->assertInstanceOf(Failure::class, $result);

		return (string) $result->getReports()[0]->messageForDeveloper;
	}

}

final class SingleTypeProvider implements ClassMapperProvider
{

	/**
	 * @param Type<mixed>|null $type
	 */
	public function __construct(
		public ?Type $type,
	)
	{
	}

	public function provide(string $className): ?Type
	{
		return $this->type; // @phpstan-ignore return.type
	}

}

/**
 * @extends Type<never>
 */
final readonly class ThrowingType extends Type
{

	/**
	 * @param non-empty-list<IssueNode> $issues
	 */
	public function __construct(
		private array $issues,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		throw new TestValidationFailed($this->issues);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode('never');
	}

}

final class TestValidationFailed extends RuntimeException implements ValidationFailed
{

	/**
	 * @param non-empty-list<IssueNode> $issues
	 */
	public function __construct(
		private readonly array $issues,
	)
	{
		parent::__construct('Validation failed.');
	}

	public function getIssues(): array
	{
		return $this->issues;
	}

}
