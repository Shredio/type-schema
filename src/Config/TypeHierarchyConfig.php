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
	public static function fromArray(array $values): ?self
	{
		$vals = [];
		foreach ($values as $key => $val) {
			if ($val instanceof TypeConfig) {
				$vals[$key] = $val;
			} else {
				$maybeHierarchy = self::fromArray($val); // @phpstan-ignore argument.type (phpstan does not support recursive types)
				if ($maybeHierarchy !== null) {
					$vals[$key] = $maybeHierarchy;
				}
			}
		}

		if ($vals === []) {
			return null;
		}

		return new self($vals);
	}

}
