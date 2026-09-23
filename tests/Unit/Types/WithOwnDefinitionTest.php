<?php declare(strict_types = 1);

namespace Tests\Unit\Types;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\Types\Type;
use Tests\TestCase;

final class WithOwnDefinitionTest extends TestCase
{

	public function testInvalidTypeOfWholeValueUsesOwnDefinition(): void
	{
		$result = $this->getProcessor()->parse('not-an-array', new OwnDefinitionPointType());

		$this->assertInstanceOf(Failure::class, $result);
		$issue = $result->getReports()[0]->issue;
		$this->assertInstanceOf(InvalidType::class, $issue);
		$this->assertSame('Point', $issue->definition->getStringType());
	}

	public function testErrorsOfChildrenKeepTheirDefinition(): void
	{
		$result = $this->getProcessor()->parse(['x' => 'one', 'y' => 2], new OwnDefinitionPointType());

		$this->assertInstanceOf(Failure::class, $result);
		$report = $result->getReports()[0];
		$this->assertSame(['x'], $report->toArrayPath());
		$this->assertInstanceOf(InvalidType::class, $report->issue);
		$this->assertSame('int', $report->issue->definition->getStringType());
	}

	public function testNullableUsesDefinitionOfWrappedType(): void
	{
		$result = $this->getProcessor()->parse('not-an-array', TypeSchema::get()->nullable(new OwnDefinitionPointType()));

		$this->assertInstanceOf(Failure::class, $result);
		$issue = $result->getReports()[0]->issue;
		$this->assertInstanceOf(InvalidType::class, $issue);
		$this->assertSame('?Point', $issue->definition->getStringType());
	}

}

/**
 * @extends Type<array{x: int, y: int}>
 */
final readonly class OwnDefinitionPointType extends Type
{

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		$t = TypeSchema::get();
		$result = $t->arrayShape(['x' => $t->int(), 'y' => $t->int()])->parse($valueToParse, $context);

		return $result instanceof Failure ? $this->withOwnDefinition($result, $context) : $result;
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode('Point');
	}

}
