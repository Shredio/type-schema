<?php declare(strict_types = 1);

namespace Tests\Unit\Context;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Context\TypeDefinition;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Mapper\RegistryClassMapperProvider;
use Shredio\TypeSchema\Types\StringType;
use Shredio\TypeSchema\Types\Type;

final class TypeDefinitionTest extends TestCase
{

	public function testTypeNodeIsUsedAsIs(): void
	{
		$definition = new TypeDefinition(new IdentifierTypeNode('int'));

		$this->assertSame('int', $definition->getStringType());
		$this->assertSame('int', $definition->getSimplifiedStringType());
		$this->assertSame('int', $definition->getUserSafeType());
	}

	public function testCallableIsResolvedLazilyAndOnlyOnce(): void
	{
		$resolutions = 0;
		$definition = new TypeDefinition(static function () use (&$resolutions): TypeNode {
			$resolutions++;

			return new IdentifierTypeNode('int');
		});

		$this->assertSame(0, $resolutions);
		$this->assertSame('int', $definition->getStringType());
		$this->assertSame('int', $definition->getStringType());
		$this->assertSame(1, $resolutions);
	}

	public function testTypeIsResolvedLazilyAndOnlyOnce(): void
	{
		$context = new TypeContext(ConversionStrategyFactory::strict(), new RegistryClassMapperProvider([]));
		$type = new ResolutionCountingType();
		$definition = new TypeDefinition($type, $context);

		$this->assertSame(0, $type->resolutions->count);
		$this->assertSame('string', $definition->getStringType());
		$this->assertSame('string', $definition->getStringType());
		$this->assertSame(1, $type->resolutions->count);
		$this->assertSame($context, $type->resolutions->context);
	}

	public function testTypeRequiresContext(): void
	{
		$definition = new TypeDefinition(new StringType());

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The type context is required to resolve the type node of a type.');

		$definition->getStringType();
	}

}

final class ResolutionLog
{

	public int $count = 0;

	public ?TypeContext $context = null;

}

/**
 * @extends Type<string>
 */
final readonly class ResolutionCountingType extends Type
{

	public ResolutionLog $resolutions;

	public function __construct()
	{
		$this->resolutions = new ResolutionLog();
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		return 'parsed';
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		$this->resolutions->count++;
		$this->resolutions->context = $context;

		return new IdentifierTypeNode('string');
	}

}
