<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Config;

final readonly class TypeHierarchyConfig
{

	/**
	 * @param array<string, TypeConfig|TypeHierarchyConfig> $configs
	 */
	public function __construct(
		public array $configs = [],
	)
	{
	}

	/**
	 * @param array<string, TypeConfig|array<string, TypeConfig|mixed[]>> $values
	 */
	public static function fromArray(array $values): self
	{
		return new self(array_map(
			fn (TypeConfig|array $vals): TypeConfig|TypeHierarchyConfig => $vals instanceof TypeConfig
				? $vals
				: self::fromArray($vals), // @phpstan-ignore argument.type (phpstan does not support recursive types)
			$values,
		));
	}

}
