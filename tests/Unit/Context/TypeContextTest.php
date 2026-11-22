<?php declare(strict_types = 1);

namespace Tests\Unit\Context;

use PHPUnit\Framework\TestCase;
use Shredio\TypeSchema\Config\TypeConfig;
use Shredio\TypeSchema\Config\TypeHierarchyConfig;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;
use Shredio\TypeSchema\Mapper\RegistryClassMapperProvider;
use stdClass;

final class TypeContextTest extends TestCase
{

	public function testDoesNotCollectErrorsByDefault(): void
	{
		$context = new TypeContext(ConversionStrategyFactory::strict(), new RegistryClassMapperProvider([]));

		$this->assertFalse($context->collectErrors);
		$this->assertNull($context->hierarchyConfig);
		$this->assertSame([], $context->getNestedContexts());
	}

	public function testWithersKeepOtherSettings(): void
	{
		$option = new stdClass();
		$hierarchy = new TypeHierarchyConfig();
		$provider = new RegistryClassMapperProvider([]);
		$context = new TypeContext(ConversionStrategyFactory::strict(), $provider, $hierarchy, [stdClass::class => $option], true);

		$withStrategy = $context->withConversionStrategy(ConversionStrategyFactory::lenient());
		$this->assertSame(ConversionStrategyFactory::lenient(), $withStrategy->conversionStrategy);
		$this->assertSame($provider, $withStrategy->classMapperProvider);
		$this->assertSame($hierarchy, $withStrategy->hierarchyConfig);
		$this->assertSame($option, $withStrategy->getOption(stdClass::class));
		$this->assertTrue($withStrategy->collectErrors);

		$otherProvider = new RegistryClassMapperProvider([]);
		$withProvider = $context->withClassMapperProvider($otherProvider);
		$this->assertSame(ConversionStrategyFactory::strict(), $withProvider->conversionStrategy);
		$this->assertSame($otherProvider, $withProvider->classMapperProvider);
		$this->assertSame($hierarchy, $withProvider->hierarchyConfig);
		$this->assertSame($option, $withProvider->getOption(stdClass::class));
		$this->assertTrue($withProvider->collectErrors);
	}

	public function testGetOptionReturnsNullForMissingOption(): void
	{
		$context = new TypeContext(ConversionStrategyFactory::strict(), new RegistryClassMapperProvider([]));

		$this->assertNull($context->getOption(stdClass::class));
	}

	public function testNestedContexts(): void
	{
		$option = new stdClass();
		$childOption = new stdClass();
		$provider = new RegistryClassMapperProvider([]);
		$grandChildren = new TypeHierarchyConfig(['leaf' => new TypeConfig()]);
		$context = new TypeContext(
			ConversionStrategyFactory::strict(),
			$provider,
			new TypeHierarchyConfig([
				'lenient' => new TypeConfig(ConversionStrategyFactory::lenient(), options: [stdClass::class => $childOption]),
				'inherited' => new TypeConfig(),
				'nested' => $grandChildren,
			]),
			[stdClass::class => $option],
			true,
		);

		$nested = $context->getNestedContexts();

		$this->assertSame(['lenient', 'inherited', 'nested'], array_keys($nested));
		$this->assertSame(ConversionStrategyFactory::lenient(), $nested['lenient']->conversionStrategy);
		$this->assertSame($childOption, $nested['lenient']->getOption(stdClass::class));
		$this->assertNull($nested['lenient']->hierarchyConfig);
		$this->assertSame(ConversionStrategyFactory::strict(), $nested['inherited']->conversionStrategy);
		$this->assertSame($grandChildren, $nested['nested']->hierarchyConfig);
		$this->assertSame($option, $nested['nested']->getOption(stdClass::class));

		foreach ($nested as $nestedContext) {
			$this->assertSame($provider, $nestedContext->classMapperProvider);
			$this->assertTrue($nestedContext->collectErrors);
		}
	}

}
