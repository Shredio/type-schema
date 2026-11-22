<?php declare(strict_types = 1);

namespace Tests\Unit\Validation\TypeSystem;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shredio\TypeSchema\Validation\TypeSystem\TypeNodeHelper;
use Tests\Unit\Validation\TypeSystem\Fixtures\First\Box;
use Tests\Unit\Validation\TypeSystem\Fixtures\First\ChildClass;
use Tests\Unit\Validation\TypeSystem\Fixtures\First\ListBox;
use Tests\Unit\Validation\TypeSystem\Fixtures\First\MapBox;

require_once __DIR__ . '/Fixtures/class-name-resolver-fixtures.php';

final class TypeNodeHelperTest extends TestCase
{

	/**
	 * @return iterable<string, array{string, bool}>
	 */
	public static function provideNullableTypes(): iterable
	{
		yield 'null' => ['null', true];
		yield 'mixed' => ['mixed', true];
		yield 'nullable' => ['?int', true];
		yield 'union with null' => ['int|string|null', true];
		yield 'union with nested nullable' => ['int|(string|null)', true];
		yield 'union without null' => ['int|string', false];
		yield 'identifier' => ['int', false];
	}

	#[DataProvider('provideNullableTypes')]
	public function testIsNullable(string $type, bool $expected): void
	{
		$this->assertSame($expected, TypeNodeHelper::isNullable($this->parse($type)));
	}

	/**
	 * @return iterable<string, array{string, string, bool}>
	 */
	public static function provideSubTypes(): iterable
	{
		yield 'member of union' => ['int', 'int|string', true];
		yield 'not member of union' => ['bool', 'int|string', false];
		yield 'union of members' => ['int|string', 'int|string|null', true];
		yield 'union with foreign member' => ['int|bool', 'int|string', false];
		yield 'shape is list' => ['array{int, string}', 'list', true];
		yield 'shape with string key is not list' => ['array{a: int}', 'list', false];
		yield 'shape with numeric string keys is list' => ["array{'0': int, '1': string}", 'list', true];
		yield 'shape with required item is non-empty list' => ['array{int, 1?: string}', 'non-empty-list', true];
		yield 'shape with only optional items is not non-empty list' => ['array{0?: int}', 'non-empty-list', false];
		yield 'shape is generic list' => ['array{int, string}', 'list<int|string>', true];
		yield 'shape with other values is not generic list' => ['array{int, string}', 'list<int>', false];
		yield 'shape is generic array' => ['array{a: int, b: string}', 'array<string, int|string>', true];
		yield 'shape with string keys is not generic list' => ['array{a: int}', 'list<int>', false];
		yield 'list is array' => ['list<int>', 'array<int, int>', true];
		yield 'list is iterable' => ['list<int>', 'iterable<int, int>', true];
		yield 'non-empty list is iterable' => ['non-empty-list<string>', 'iterable<int, string>', true];
		yield 'list of other values is not array' => ['list<string>', 'array<int, int>', false];
		yield 'list does not have string keys' => ['list<int>', 'array<string, int>', false];
		yield 'covariant value' => ['array<int, int>', 'array<int, int|string>', true];
		yield 'class generic implements interface generic' => [sprintf('%s<int>', ListBox::class), sprintf('%s<int>', Box::class), true];
		yield 'class generic with other parameter' => [sprintf('%s<int>', ListBox::class), sprintf('%s<string>', Box::class), false];
		yield 'second template is mapped' => [sprintf('%s<string, int>', MapBox::class), sprintf('%s<int>', Box::class), true];
	}

	#[DataProvider('provideSubTypes')]
	public function testIsSubTypeOf(string $a, string $b, bool $expected): void
	{
		$this->assertSame($expected, TypeNodeHelper::isSubTypeOf($this->parse($a), $this->parse($b)));
	}

	/**
	 * @return iterable<string, array{string, string, int, string}>
	 */
	public static function provideGenericParameters(): iterable
	{
		yield 'value of list' => ['list<string>', 'array', 1, 'string'];
		yield 'key of list' => ['list<string>', 'array', 0, 'int'];
		yield 'union of lists' => ['list<int>|list<string>', 'array', 1, '(int | string)'];
		yield 'super type parameter' => [sprintf('%s<float>', ListBox::class), Box::class, 0, 'float'];
		yield 'mapped template' => [sprintf('%s<string, bool>', MapBox::class), Box::class, 0, 'bool'];
	}

	#[DataProvider('provideGenericParameters')]
	public function testInferGenericParameter(string $type, string $typeName, int $parameter, string $expected): void
	{
		$this->assertSame($expected, (string) TypeNodeHelper::inferGenericParameter($this->parse($type), $typeName, $parameter));
	}

	public function testGenericSuperTypesAreKeyedByName(): void
	{
		$superTypes = TypeNodeHelper::getGenericTypeSuperTypes($this->parse(sprintf('%s<int>', ListBox::class)));

		$this->assertSame([Box::class], array_keys($superTypes));
		$this->assertSame(sprintf('%s<int>', Box::class), (string) $superTypes[Box::class]);
	}

	public function testGenericDefinitionResolvesBoundsFromPhpDoc(): void
	{
		$definition = TypeNodeHelper::getGenericTypeDefinition($this->parse(MapBox::class));

		$this->assertSame('Shredio\\TypeSchema\\Types\\Type', (string) $definition->parameters[1]->bound);
		$this->assertSame([Box::class => [1]], $definition->extends);
	}

	public function testResolveClassKeywords(): void
	{
		$type = $this->parse('array<IssueNode, list<self>>|BaseIssue|SchemaTypes\\Type|T|int|\\Foo');

		TypeNodeHelper::resolveClassKeywords($type, new ReflectionClass(ChildClass::class), ['T']);

		$this->assertSame(
			sprintf('(array<Shredio\\TypeSchema\\Issue\\IssueNode, list<%s>> | Shredio\\TypeSchema\\Issue\\Issue | Shredio\\TypeSchema\\Types\\Type | T | int | Foo)', ChildClass::class),
			(string) $type,
		);
	}

	private function parse(string $type): TypeNode
	{
		$config = new ParserConfig([]);
		$typeParser = new TypeParser($config, new ConstExprParser($config));

		return $typeParser->parse(new TokenIterator((new Lexer($config))->tokenize($type)));
	}

}
