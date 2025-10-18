<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Context;

use Shredio\TypeSchema\Config\TypeHierarchyConfig;
use Shredio\TypeSchema\Conversion\ConversionStrategy;
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
	)
	{
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
				);
			} else {
				$contexts[$key] = new self(
					$childConfig->conversionStrategy ?? $this->conversionStrategy,
					$this->errorElementFactory,
					$this->classMapperProvider,
					null,
					$childConfig->options,
					$this->collectErrors,
				);
			}
		}

		return $contexts;
	}

}
