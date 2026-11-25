<?php declare(strict_types = 1);

namespace Tests\Unit\Mapper\Jit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Mapper\ClassMapperProvider;
use Shredio\TypeSchema\Mapper\Jit\ClassMapperCompiler;
use Shredio\TypeSchema\Mapper\Jit\ClassMapperCompileHashedTargetProvider;
use Shredio\TypeSchema\Mapper\Jit\ClassMapperCompileStaticTargetProvider;
use Shredio\TypeSchema\Mapper\Jit\ClassMapperToCompile;
use Shredio\TypeSchema\Mapper\Jit\JustInTimeClassMapperProvider;
use Shredio\TypeSchema\Mapper\Jit\ObjectMapperCompilerContext;
use Shredio\TypeSchema\Mapper\RegistryClassMapperProvider;
use Shredio\TypeSchema\Types\Type;
use stdClass;

final class JustInTimeClassMapperProviderTest extends TestCase
{

	private string $directory;

	private ClassMapperCompileStaticTargetProvider $targetProvider;

	protected function setUp(): void
	{
		$uniqueId = bin2hex(random_bytes(6));
		$this->directory = sprintf('%s/type-schema-jit-%s', sys_get_temp_dir(), $uniqueId);
		mkdir($this->directory);
		$this->targetProvider = new ClassMapperCompileStaticTargetProvider(
			sprintf('%s/', $this->directory),
			sprintf('JitTest%s\\%%sMapper', $uniqueId),
		);
	}

	protected function tearDown(): void
	{
		foreach (glob(sprintf('%s/*.php', $this->directory)) ?: [] as $file) {
			unlink($file);
		}

		rmdir($this->directory);
	}

	public function testCompilesMissingMapperAndLoadsIt(): void
	{
		$compiler = new FakeCompiler();
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, $compiler);

		$mapper = $provider->provide(stdClass::class);

		$this->assertSame($this->targetProvider->provide(stdClass::class)->mapperClassName, $mapper::class);
		$this->assertSame('compiled stdClass', $this->parse($mapper));
		$this->assertSame(['needsRecompile stdClass', 'compile stdClass'], $compiler->log->entries);
	}

	public function testDeclaredMapperIsReturnedWithoutCompiling(): void
	{
		$compiler = new FakeCompiler();
		(new JustInTimeClassMapperProvider($this->targetProvider, $compiler))->provide(stdClass::class);

		$mapper = (new JustInTimeClassMapperProvider($this->targetProvider, $compiler))->provide(stdClass::class);

		$this->assertSame('compiled stdClass', $this->parse($mapper));
		$this->assertSame(['needsRecompile stdClass', 'compile stdClass'], $compiler->log->entries);
	}

	public function testMapperIsResolvedOnlyOnceForClass(): void
	{
		$inner = new CountingClassMapperProvider();
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, new FakeCompiler(), $inner);
		$mapper = $provider->provide(stdClass::class);
		$lookupsOfFirstCall = $inner->lookups;

		$this->assertSame($mapper, $provider->provide(stdClass::class));
		$this->assertGreaterThan(0, $lookupsOfFirstCall);
		$this->assertSame($lookupsOfFirstCall, $inner->lookups);
	}

	public function testExistingFileIsLoadedWithoutCompiling(): void
	{
		$compiler = new FakeCompiler();
		$compiler->writeMapper($this->targetProvider->provide(stdClass::class));
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, $compiler);

		$mapper = $provider->provide(stdClass::class);

		$this->assertSame('compiled stdClass', $this->parse($mapper));
		$this->assertSame(['needsRecompile stdClass'], $compiler->log->entries);
	}

	public function testExistingFileIsRecompiledWhenCompilerRequestsIt(): void
	{
		$compiler = new FakeCompiler(needsRecompile: true);
		$compiler->writeMapper($this->targetProvider->provide(stdClass::class));
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, $compiler);

		$provider->provide(stdClass::class);

		$this->assertSame(['needsRecompile stdClass', 'compile stdClass'], $compiler->log->entries);
	}

	public function testInnerProviderHasPriority(): void
	{
		$compiler = new FakeCompiler();
		$inner = new RegistryClassMapperProvider(RegistryClassMapperProvider::createDefaultClassMappers());
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, $compiler, $inner);

		$mapper = $provider->provide(DateTimeImmutable::class);

		$this->assertInstanceOf(DateTimeImmutable::class, $mapper->parse('2024-01-15', $this->createContext()));
		$this->assertSame([], $compiler->log->entries);
	}

	public function testCompilerContextKnowsWhichClassesHaveProvider(): void
	{
		$compiler = new FakeCompiler();
		$inner = new RegistryClassMapperProvider(RegistryClassMapperProvider::createDefaultClassMappers());
		$compiler->onCompile = static function (ObjectMapperCompilerContext $context) use ($compiler): void {
			$compiler->log->entries[] = sprintf(
				'date has provider: %s',
				var_export($context->hasProviderFor($context->createClassMapperToCompile(DateTimeImmutable::class)), true),
			);
			$compiler->log->entries[] = sprintf(
				'runtime exception has provider: %s',
				var_export($context->hasProviderFor($context->createClassMapperToCompile(RuntimeException::class)), true),
			);
		};
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, $compiler, $inner);

		$provider->provide(stdClass::class);

		$this->assertSame([
			'needsRecompile stdClass',
			'date has provider: true',
			'needsRecompile RuntimeException',
			'runtime exception has provider: false',
			'compile stdClass',
		], $compiler->log->entries);
	}

	public function testWarmupRecompilesWithoutMultiProcessSafety(): void
	{
		$compiler = new FakeCompiler();
		$compiler->writeMapper($this->targetProvider->provide(stdClass::class));
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, $compiler);

		$provider->warmup(stdClass::class);

		$this->assertSame(['compile stdClass without multi-process safety'], $compiler->log->entries);
	}

	public function testWarmupWithoutForceKeepsUpToDateFile(): void
	{
		$compiler = new FakeCompiler();
		$compiler->writeMapper($this->targetProvider->provide(stdClass::class));
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, $compiler);

		$provider->warmup(stdClass::class, forceRecompile: false);

		$this->assertSame(['needsRecompile stdClass'], $compiler->log->entries);
	}

	public function testValidateCompilesInValidationModeWithoutLoadingMapper(): void
	{
		$compiler = new FakeCompiler();
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, $compiler);

		$provider->validate(stdClass::class);

		$this->assertSame(['compile stdClass in validation mode'], $compiler->log->entries);
		$this->assertFalse(class_exists($this->targetProvider->provide(stdClass::class)->mapperClassName, false));
	}

	public function testWarnsAboutJustInTimeCompilation(): void
	{
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, new FakeCompiler(), raiseWarningsOnMissingClasses: true);
		$warnings = [];
		set_error_handler(static function (int $level, string $message) use (&$warnings): bool {
			$warnings[] = [$level, $message];

			return true;
		});

		try {
			$provider->provide(stdClass::class);
		} finally {
			restore_error_handler();
		}

		$this->assertSame([[
			E_USER_WARNING,
			'Class stdClass is being compiled just-in-time. Consider pre-compiling it to improve performance.',
		]], $warnings);
	}

	public function testDoesNotWarnByDefault(): void
	{
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, new FakeCompiler());
		$warnings = 0;
		set_error_handler(static function () use (&$warnings): bool {
			$warnings++;

			return true;
		});

		try {
			$provider->provide(stdClass::class);
		} finally {
			restore_error_handler();
		}

		$this->assertSame(0, $warnings);
	}

	public function testThrowsWhenCompilerProducesNoFile(): void
	{
		$compiler = new FakeCompiler(writesFile: false);
		$provider = new JustInTimeClassMapperProvider($this->targetProvider, $compiler);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Cannot provide object mapper for class stdClass');

		$provider->provide(stdClass::class);
	}

	public function testStaticTargetProvider(): void
	{
		$provider = new ClassMapperCompileStaticTargetProvider('/var/cache/mappers/', 'Generated\\%sMapper');

		$target = $provider->provide('App\\Entity\\User');

		$this->assertSame('App\\Entity\\User', $target->className);
		$this->assertSame('Generated\\UserMapper', $target->mapperClassName);
		$this->assertSame('/var/cache/mappers/UserMapper.php', $target->targetFilePath);
		$this->assertSame('Generated\\stdClassMapper', $provider->provide(stdClass::class)->mapperClassName);
	}

	public function testHashedTargetProviderDistinguishesClassesWithSameShortName(): void
	{
		$provider = new ClassMapperCompileHashedTargetProvider('/var/cache/mappers', 'Generated\\%sMapper');
		$hash = substr(md5('App\\Entity\\User'), 0, 10);

		$target = $provider->provide('App\\Entity\\User');

		$this->assertSame('App\\Entity\\User', $target->className);
		$this->assertSame(sprintf('Generated\\UserMapper_%s', $hash), $target->mapperClassName);
		$this->assertSame(sprintf('/var/cache/mappers/UserMapper_%s.php', $hash), $target->targetFilePath);
		$this->assertNotSame($target->mapperClassName, $provider->provide('App\\Api\\User')->mapperClassName);
		$this->assertStringStartsWith('Generated\\stdClassMapper_', $provider->provide(stdClass::class)->mapperClassName);
		$this->assertSame(
			$target->targetFilePath,
			(new ClassMapperCompileHashedTargetProvider('/var/cache/mappers/', 'Generated\\%sMapper'))->provide('App\\Entity\\User')->targetFilePath,
		);
	}

	/**
	 * @param Type<mixed> $mapper
	 */
	private function parse(Type $mapper): mixed
	{
		return $mapper->parse(null, $this->createContext());
	}

	private function createContext(): TypeContext
	{
		/** @var ClassMapperProvider $provider */
		$provider = new RegistryClassMapperProvider([]);

		return new TypeContext(ConversionStrategyFactory::lenient(), $provider);
	}

}

final class CountingClassMapperProvider implements ClassMapperProvider
{

	public int $lookups = 0;

	public function provide(string $className): ?Type
	{
		$this->lookups++;

		return null;
	}

}

final class CompilerLog
{

	/** @var list<string> */
	public array $entries = [];

}

final class FakeCompiler implements ClassMapperCompiler
{

	public CompilerLog $log;

	/** @var (callable(ObjectMapperCompilerContext): void)|null */
	public mixed $onCompile = null;

	private bool $multiProcessSafety = true;

	private bool $validationMode = false;

	public function __construct(
		private readonly bool $needsRecompile = false,
		private readonly bool $writesFile = true,
	)
	{
		$this->log = new CompilerLog();
	}

	public function compile(ClassMapperToCompile $objectMapperData, ObjectMapperCompilerContext $context): void
	{
		if ($this->onCompile !== null) {
			($this->onCompile)($context);
		}

		$entry = sprintf('compile %s', $objectMapperData->className);
		if (!$this->multiProcessSafety) {
			$entry = sprintf('%s without multi-process safety', $entry);
		}

		if ($this->validationMode) {
			$this->log->entries[] = sprintf('%s in validation mode', $entry);

			return;
		}

		$this->log->entries[] = $entry;
		if ($this->writesFile) {
			$this->writeMapper($objectMapperData);
		}
	}

	public function needsRecompile(ClassMapperToCompile $objectMapperData): bool
	{
		$this->log->entries[] = sprintf('needsRecompile %s', $objectMapperData->className);

		return $this->needsRecompile;
	}

	public function withMultiProcessSafety(bool $enabled): static
	{
		$compiler = clone $this;
		$compiler->multiProcessSafety = $enabled;

		return $compiler;
	}

	public function withValidationMode(): static
	{
		$compiler = clone $this;
		$compiler->validationMode = true;

		return $compiler;
	}

	/**
	 * @param ClassMapperToCompile<object> $objectMapperData
	 */
	public function writeMapper(ClassMapperToCompile $objectMapperData): void
	{
		$position = strrpos($objectMapperData->mapperClassName, '\\');
		$namespace = $position === false ? '' : substr($objectMapperData->mapperClassName, 0, $position);
		$shortName = $position === false ? $objectMapperData->mapperClassName : substr($objectMapperData->mapperClassName, $position + 1);

		file_put_contents($objectMapperData->targetFilePath, sprintf(<<<'PHP'
<?php declare(strict_types = 1);

namespace %s;

final readonly class %s extends \Shredio\TypeSchema\Types\Type
{

	public function parse(mixed $valueToParse, \Shredio\TypeSchema\Context\TypeContext $context): mixed
	{
		return %s;
	}

	protected function getTypeNode(\Shredio\TypeSchema\Context\TypeContext $context): \PHPStan\PhpDocParser\Ast\Type\TypeNode
	{
		return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('string');
	}

}

PHP, $namespace, $shortName, var_export(sprintf('compiled %s', $objectMapperData->className), true)));
	}

}
