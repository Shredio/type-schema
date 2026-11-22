<?php declare(strict_types = 1);

namespace Tests\Unit\Validation\TypeSystem;

use ArrayObject;
use ClassNameResolverGlobalFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Validation\TypeSystem\ClassNameResolver;
use Tests\Unit\Validation\TypeSystem\Fixtures\First\Box;
use Tests\Unit\Validation\TypeSystem\Fixtures\First\ChildClass;
use Tests\Unit\Validation\TypeSystem\Fixtures\First\ParentClass;
use Tests\Unit\Validation\TypeSystem\Fixtures\First\SomeTrait;
use Tests\Unit\Validation\TypeSystem\Fixtures\Second\SecondEnum;
use Tests\Unit\Validation\TypeSystem\Fixtures\Second\SecondInterface;

require_once __DIR__ . '/Fixtures/class-name-resolver-fixtures.php';

final class ClassNameResolverTest extends TestCase
{

	private const string FirstNamespace = 'Tests\\Unit\\Validation\\TypeSystem\\Fixtures\\First';
	private const string SecondNamespace = 'Tests\\Unit\\Validation\\TypeSystem\\Fixtures\\Second';

	/**
	 * @return iterable<string, array{class-string, string, string}>
	 */
	public static function provideNames(): iterable
	{
		yield 'imported class' => [ChildClass::class, 'DateTimeImmutable', 'DateTimeImmutable'];
		yield 'imported namespace alias' => [ChildClass::class, 'SchemaTypes\\Type', 'Shredio\\TypeSchema\\Types\\Type'];
		yield 'group use' => [ChildClass::class, 'IssueNode', 'Shredio\\TypeSchema\\Issue\\IssueNode'];
		yield 'group use alias' => [ChildClass::class, 'BaseIssue', 'Shredio\\TypeSchema\\Issue\\Issue'];
		yield 'comma separated use' => [ChildClass::class, 'Success', 'Shredio\\TypeSchema\\Result\\Success'];
		yield 'comma separated use alias' => [ChildClass::class, 'ParseFailure', 'Shredio\\TypeSchema\\Result\\Failure'];
		yield 'imported class with inner name' => [ChildClass::class, 'Success\\Inner', 'Shredio\\TypeSchema\\Result\\Success\\Inner'];
		yield 'class from same namespace' => [ChildClass::class, 'Unknown', sprintf('%s\\Unknown', self::FirstNamespace)];
		yield 'relative name' => [ChildClass::class, 'Sub\\Unknown', sprintf('%s\\Sub\\Unknown', self::FirstNamespace)];
		yield 'fully qualified name' => [ChildClass::class, '\\Foo\\Bar', 'Foo\\Bar'];
		yield 'function import is not class import' => [ChildClass::class, 'sprintf', sprintf('%s\\sprintf', self::FirstNamespace)];
		yield 'trait use is not import' => [ChildClass::class, 'SomeTrait', sprintf('%s\\SomeTrait', self::FirstNamespace)];
		yield 'trait use is not import for following classes' => [Box::class, 'SomeTrait', sprintf('%s\\SomeTrait', self::FirstNamespace)];
		yield 'use with leading backslash' => [ChildClass::class, 'WithNotices', 'Shredio\\TypeSchema\\Result\\WithNotices'];
		yield 'use after class applies to following classes' => [Box::class, 'LateImport', 'Shredio\\TypeSchema\\TypeSchema'];
		yield 'use after class does not apply to the class' => [ChildClass::class, 'LateImport', sprintf('%s\\LateImport', self::FirstNamespace)];
		yield 'trait context' => [SomeTrait::class, 'IssueNode', 'Shredio\\TypeSchema\\Issue\\IssueNode'];
		yield 'builtin type' => [ChildClass::class, 'INT', 'int'];
		yield 'mixed' => [ChildClass::class, 'Mixed', 'mixed'];
		yield 'self' => [ChildClass::class, 'self', ChildClass::class];
		yield 'static' => [ChildClass::class, 'static', ChildClass::class];
		yield 'parent' => [ChildClass::class, 'parent', ParentClass::class];
		yield 'parent without parent class' => [ParentClass::class, 'parent', 'parent'];
		yield 'uses of other namespace block' => [SecondInterface::class, 'ParentClass', 'Shredio\\TypeSchema\\Issue\\InvalidType'];
		yield 'uses do not leak between namespace blocks' => [SecondInterface::class, 'IssueNode', sprintf('%s\\IssueNode', self::SecondNamespace)];
		yield 'enum context' => [SecondEnum::class, 'ParentClass', 'Shredio\\TypeSchema\\Issue\\InvalidType'];
		yield 'global namespace import' => [ClassNameResolverGlobalFixture::class, 'TypeSchema', 'Shredio\\TypeSchema\\TypeSchema'];
		yield 'global namespace class' => [ClassNameResolverGlobalFixture::class, 'Foo', 'Foo'];
		yield 'internal class' => [ArrayObject::class, 'Foo', 'Foo'];
	}

	/**
	 * @param class-string $contextClass
	 */
	#[DataProvider('provideNames')]
	public function testResolve(string $contextClass, string $name, string $expected): void
	{
		$context = new ReflectionClass($contextClass);

		$this->assertSame($expected, ClassNameResolver::resolve($name, $context));
		$this->assertSame($expected, ClassNameResolver::resolve($name, $context), 'Cached use statements give the same result.');
	}

	public function testEmptyNameIsRejected(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Class name must not be empty.');

		ClassNameResolver::resolve('', new ReflectionClass(ChildClass::class));
	}

	public function testAnonymousClassIsRejected(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Anonymous classes are not supported.');

		ClassNameResolver::resolve('Foo', new ReflectionClass(new class {}));
	}

}
