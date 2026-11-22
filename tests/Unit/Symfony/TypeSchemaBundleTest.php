<?php declare(strict_types = 1);

namespace Tests\Unit\Symfony;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Conversion\ConversionStrategy;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Exception\AssertException;
use Shredio\TypeSchema\Issue\Renderer\IssueRenderer;
use Shredio\TypeSchema\Issue\Renderer\SymfonyIssueRenderer;
use Shredio\TypeSchema\Mapper\ClassMapper;
use Shredio\TypeSchema\Mapper\ClassMapperProvider;
use Shredio\TypeSchema\Symfony\SymfonyPropertyConstraints;
use Shredio\TypeSchema\Symfony\TypeSchemaBundle;
use Shredio\TypeSchema\TypeSchema;
use Shredio\TypeSchema\TypeSchemaProcessor;
use stdClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\Unit\Mapper\Fixtures\TestBackedStringEnum;
use Tests\Unit\Symfony\Fixtures\UserFixture;

final class TypeSchemaBundleTest extends TestCase
{

	public function testRegistersProcessorWithStrictStrategyAndSymfonyRenderer(): void
	{
		$container = $this->createContainer();

		$processor = $container->get(TypeSchemaProcessor::class);

		$this->assertInstanceOf(TypeSchemaProcessor::class, $processor);
		$this->assertSame(ConversionStrategyFactory::strict(), $container->get(ConversionStrategy::class));
		$this->assertInstanceOf(SymfonyIssueRenderer::class, $container->get(IssueRenderer::class));
		$this->assertSame($container->get(IssueRenderer::class), $processor->getIssueRenderer());
		$this->assertFalse($processor->matches('5', TypeSchema::get()->int()));

		try {
			$processor->process('x', TypeSchema::get()->int());
			$this->fail('AssertException was not thrown.');
		} catch (AssertException $exception) {
			$this->assertSame('This value should be of type int.', (string) $exception->getErrors()[0]->message);
		}
	}

	public function testRegistersDefaultClassMappers(): void
	{
		$container = $this->createContainer();
		$provider = $container->get(ClassMapperProvider::class);

		$this->assertInstanceOf(ClassMapperProvider::class, $provider);
		$this->assertSame($provider, $container->get(TypeSchemaBundle::ClassMapperProviderServiceName));
		$context = new TypeContext(ConversionStrategyFactory::lenient(), $provider);
		$this->assertSame(TestBackedStringEnum::First, $provider->provide(TestBackedStringEnum::class)?->parse('first', $context));
		$this->assertInstanceOf(DateTimeImmutable::class, $provider->provide(DateTimeImmutable::class)?->parse('2024-01-15', $context));
	}

	public function testAutoconfiguredClassMappersAreRegistered(): void
	{
		$container = $this->createContainer(static function (ContainerBuilder $builder): void {
			$builder->register(BundleTestStdClassMapper::class)->setAutoconfigured(true);
		});
		$provider = $container->get(ClassMapperProvider::class);

		$this->assertInstanceOf(ClassMapperProvider::class, $provider);
		$this->assertNotNull($provider->provide(stdClass::class));
		$this->assertTrue($container->getDefinition(BundleTestStdClassMapper::class)->hasTag(TypeSchemaBundle::ClassMapperTag));
	}

	public function testSymfonyValidatorIsDefaultOption(): void
	{
		$processor = $this->createContainer()->get(TypeSchemaProcessor::class);
		$this->assertInstanceOf(TypeSchemaProcessor::class, $processor);
		$type = TypeSchema::get()->string()->validate(new SymfonyPropertyConstraints(UserFixture::class, 'name'));

		$this->assertTrue($processor->matches('hello', $type));
		$this->assertFalse($processor->matches('', $type));
	}

	/**
	 * @param (callable(ContainerBuilder): void)|null $configure
	 */
	private function createContainer(?callable $configure = null): ContainerBuilder
	{
		$builder = new ContainerBuilder();
		$builder->setParameter('kernel.environment', 'test');
		$builder->setParameter('kernel.build_dir', sys_get_temp_dir());
		$builder->register('translator', IdentityTranslator::class);
		$builder->register(ValidatorInterface::class)->setSynthetic(true);

		$bundle = new TypeSchemaBundle();
		$extension = $bundle->getContainerExtension();
		$this->assertNotNull($extension);
		$builder->registerExtension($extension);
		$builder->loadFromExtension($extension->getAlias());

		if ($configure !== null) {
			$configure($builder);
		}

		$builder->addCompilerPass(new class implements CompilerPassInterface {

			public function process(ContainerBuilder $container): void
			{
				foreach ($container->getDefinitions() as $definition) {
					$definition->setPublic(true);
				}

				foreach ($container->getAliases() as $alias) {
					$alias->setPublic(true);
				}
			}

		});
		$builder->compile();
		$builder->set(ValidatorInterface::class, Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator());

		return $builder;
	}

}

/**
 * @extends ClassMapper<stdClass>
 */
final readonly class BundleTestStdClassMapper extends ClassMapper
{

	public function isSupported(string $className): bool
	{
		return $className === stdClass::class;
	}

	public function create(string $className, mixed $valueToParse, TypeContext $context): object
	{
		return new stdClass();
	}

}
