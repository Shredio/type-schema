<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Config;

use Shredio\TypeSchema\Conversion\ConversionStrategy;
use Shredio\TypeSchema\Mapper\ObjectMapperProvider;

readonly class TypeConfig
{

	/**
	 * @param array<class-string, object> $options
	 */
	public function __construct(
		public ?ConversionStrategy $conversionStrategy = null,
		public ?ObjectMapperProvider $objectMapperProvider = null,
		public ?TypeHierarchyConfig $hierarchyConfig = null,
		public array $options = [],
	)
	{
	}

	/**
	 * @param list<object> $options
	 * @return array<class-string, object>
	 */
	public static function buildOptions(array $options): array
	{
		$return = [];
		foreach ($options as $option) {
			$return[$option::class] = $option;
		}
		return $return;
	}

	public function withConversionStrategy(?ConversionStrategy $conversionStrategy): TypeConfig
	{
		return new self(
			$conversionStrategy,
			$this->objectMapperProvider,
			$this->hierarchyConfig,
			$this->options,
		);
	}

	public function withObjectMapperProvider(?ObjectMapperProvider $objectMapperProvider): TypeConfig
	{
		return new self(
			$this->conversionStrategy,
			$objectMapperProvider,
			$this->hierarchyConfig,
		);
	}

	public function withOption(object $option): TypeConfig
	{
		$options = $this->options;
		$options[$option::class] = $option;

		return new self(
			$this->conversionStrategy,
			$this->objectMapperProvider,
			$this->hierarchyConfig,
			$options,
		);
	}

	/**
	 * @param array<string, TypeConfig|array<string, TypeConfig|mixed[]>> $values
	 */
	public function withHierarchy(array $values): TypeConfig
	{
		return new self(
			$this->conversionStrategy,
			$this->objectMapperProvider,
			TypeHierarchyConfig::fromArray($values),
		);
	}

}
