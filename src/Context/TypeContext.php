<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Context;

use Shredio\TypeSchema\Config\TypeHierarchyConfig;
use Shredio\TypeSchema\Conversion\ConversionStrategy;
use Shredio\TypeSchema\Enum\ExtraKeysBehavior;
use Shredio\TypeSchema\Mapper\ClassMapperProvider;
use Shredio\TypeSchema\Validation\ErrorElementFactory;

final readonly class TypeContext
{

	/**
	 * @param array<class-string, object> $options
	 */
	public function __construct(
		public ConversionStrategy $conversionStrategy,
		public ErrorElementFactory $errorElementFactory,
		public ClassMapperProvider $classMapperProvider,
		public ?TypeHierarchyConfig $hierarchyConfig = null,
		private array $options = [],
		public bool $collectErrors = false,
		public ?ExtraKeysBehavior $defaultExtraKeysBehavior = null,
	)
	{
	}

	public function withConversionStrategy(ConversionStrategy $conversionStrategy): TypeContext
	{
		return new self(
			$conversionStrategy,
			$this->errorElementFactory,
			$this->classMapperProvider,
			$this->hierarchyConfig,
			$this->options,
			$this->collectErrors,
			$this->defaultExtraKeysBehavior,
		);
	}

	public function withErrorElementFactory(ErrorElementFactory $errorElementFactory): TypeContext
	{
		return new self(
			$this->conversionStrategy,
			$errorElementFactory,
			$this->classMapperProvider,
			$this->hierarchyConfig,
			$this->options,
			$this->collectErrors,
			$this->defaultExtraKeysBehavior,
		);
	}

	public function withClassMapperProvider(ClassMapperProvider $classMapperProvider): TypeContext
	{
		return new self(
			$this->conversionStrategy,
			$this->errorElementFactory,
			$classMapperProvider,
			$this->hierarchyConfig,
			$this->options,
			$this->collectErrors,
			$this->defaultExtraKeysBehavior,
		);
	}

	public function withDefaultExtraKeysBehavior(?ExtraKeysBehavior $behavior): TypeContext
	{
		return new self(
			$this->conversionStrategy,
			$this->errorElementFactory,
			$this->classMapperProvider,
			$this->hierarchyConfig,
			$this->options,
			$this->collectErrors,
			$behavior,
		);
	}

	/**
	 * @template TOption of object
	 * @param class-string<TOption> $className
	 * @return TOption|null
	 */
	public function getOption(string $className): ?object
	{
		/** @var TOption|null */
		return $this->options[$className] ?? null;
	}

	/**
	 * @return array<string, TypeContext>
	 */
	public function getNestedContexts(): array
	{
		if ($this->hierarchyConfig === null) {
			return [];
		}

		$contexts = [];
		foreach ($this->hierarchyConfig->configs as $key => $childConfig) {
			if ($childConfig instanceof TypeHierarchyConfig) {
				$contexts[$key] = new self(
					$this->conversionStrategy,
					$this->errorElementFactory,
					$this->classMapperProvider,
					$childConfig,
					$this->options,
					$this->collectErrors,
					$this->defaultExtraKeysBehavior,
				);
			} else {
				$contexts[$key] = new self(
					$childConfig->conversionStrategy ?? $this->conversionStrategy,
					$this->errorElementFactory,
					$this->classMapperProvider,
					null,
					$childConfig->options,
					$this->collectErrors,
					$this->defaultExtraKeysBehavior,
				);
			}
		}

		return $contexts;
	}

}
