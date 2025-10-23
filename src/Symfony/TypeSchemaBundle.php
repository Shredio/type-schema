<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Symfony;

use Shredio\TypeSchema\Conversion\ConversionStrategy;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Mapper\BackedEnumClassMapper;
use Shredio\TypeSchema\Mapper\ClassMapper;
use Shredio\TypeSchema\Mapper\ClassMapperProvider;
use Shredio\TypeSchema\Mapper\DateTimeClassMapper;
use Shredio\TypeSchema\Mapper\RegistryClassMapperProvider;
use Shredio\TypeSchema\TypeSchemaProcessor;
use Shredio\TypeSchema\Validation\SymfonyErrorElementFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

final class TypeSchemaBundle extends AbstractBundle
{

	public const string ClassMapperProviderServiceName = 'type_schema.class_mapper_provider';
	public const string ClassMapperTag = 'type_schema.class_mapper';

	/**
	 * @param mixed[] $config
	 */
	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$services->set($this->prefix('conversion_strategy'), ConversionStrategy::class)
			->factory([ConversionStrategyFactory::class, 'strict'])
			->alias(ConversionStrategy::class, $this->prefix('conversion_strategy'));

		$services->set($this->prefix('error_element_factory'), SymfonyErrorElementFactory::class)
			->arg('$translator', service('translator'));

		$services->set(self::ClassMapperProviderServiceName, RegistryClassMapperProvider::class)
			->arg('$mappers', tagged_iterator(self::ClassMapperTag))
			->alias(ClassMapperProvider::class, self::ClassMapperProviderServiceName);

		$this->registerClassMapper($services, 'backed_enum', BackedEnumClassMapper::class);
		$this->registerClassMapper($services, 'datetime', DateTimeClassMapper::class);

		$services->set($this->prefix('processor'), TypeSchemaProcessor::class)
			->arg('$conversionStrategy', service($this->prefix('conversion_strategy')))
			->arg('$errorElementFactory', service($this->prefix('error_element_factory')))
			->arg('$classMapperProvider', service(self::ClassMapperProviderServiceName))
			->alias(TypeSchemaProcessor::class, $this->prefix('processor'));

		$builder->registerForAutoconfiguration(ClassMapper::class)
			->addTag(self::ClassMapperTag);
	}

	private function prefix(string $name): string
	{
		return sprintf('type_schema.%s', $name);
	}

	/**
	 * @param class-string<ClassMapper<covariant object>> $className
	 */
	private function registerClassMapper(ServicesConfigurator $services, string $name, string $className): void
	{
		$services->set($this->prefix(sprintf('class_mapper.%s', $name)), $className)
			->tag(self::ClassMapperTag);
	}

}
