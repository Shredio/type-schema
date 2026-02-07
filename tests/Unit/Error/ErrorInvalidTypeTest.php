<?php declare(strict_types = 1);

namespace Tests\Unit\Error;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Error\ErrorInvalidType;
use Shredio\TypeSchema\Error\ErrorReportConfig;
use Shredio\TypeSchema\Error\Path;

#[CoversClass(ErrorInvalidType::class)]
final class ErrorInvalidTypeTest extends TestCase
{

	public function testGetReportsContainsExpectedType(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = new ErrorInvalidType(
			$definition,
			fn (?string $type): string => sprintf('Expected %s.', $type ?? 'unknown'),
			42,
		);

		$reports = $error->getReports();

		$this->assertCount(1, $reports);
		$this->assertSame('Expected string.', (string) $reports[0]->message);
	}

	public function testGetReportsWithPath(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = new ErrorInvalidType(
			$definition,
			fn (?string $type): string => 'Invalid',
			42,
		);

		$path = [new Path('field')];
		$reports = $error->getReports($path);

		$this->assertCount(1, $reports);
		$this->assertSame($path, $reports[0]->path);
	}

	public function testGetReportsHidesExpectedTypeWithConfig(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = new ErrorInvalidType(
			$definition,
			fn (?string $type): string => sprintf('Expected %s.', $type ?? 'nothing'),
			42,
		);

		$config = new ErrorReportConfig(exposeExpectedType: false);
		$reports = $error->getReports([], $config);

		$this->assertSame('Expected nothing.', (string) $reports[0]->message);
	}

	public function testWithDefinitionReturnsNewInstance(): void
	{
		$definition1 = new TypeDefinition(new IdentifierTypeNode('string'));
		$definition2 = new TypeDefinition(new IdentifierTypeNode('int'));
		$error = new ErrorInvalidType(
			$definition1,
			fn (?string $type): string => sprintf('Expected %s.', $type ?? 'unknown'),
			42,
		);

		$changed = $error->withDefinition($definition2);
		$reports = $changed->getReports();

		$this->assertSame('Expected int.', (string) $reports[0]->message);
	}

	public function testCustomTypeSeparator(): void
	{
		$definition = new TypeDefinition(new UnionTypeNode([
			new IdentifierTypeNode('string'),
			new IdentifierTypeNode('int'),
		]));
		$error = new ErrorInvalidType(
			$definition,
			fn (?string $type): string => sprintf('Expected %s.', $type ?? 'unknown'),
			null,
			', ',
		);

		$reports = $error->getReports();

		$this->assertStringContainsString(', ', (string) $reports[0]->message);
	}

	public function testDeveloperMessageIncludesOriginalValue(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('string'));
		$error = new ErrorInvalidType(
			$definition,
			fn (?string $type): string => 'Invalid',
			42,
		);

		$reports = $error->getReports();

		$this->assertStringContainsString('42', (string) $reports[0]->messageForDeveloper);
		$this->assertStringContainsString('string', (string) $reports[0]->messageForDeveloper);
	}

}
