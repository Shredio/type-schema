<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion;

interface ConversionStrategy
{

	public function string(mixed $value): ?string;

	public function int(mixed $value): ?int;

	public function float(mixed $value): ?float;

	public function bool(mixed $value): ?bool;

	public function null(mixed $value): null|false;

	/**
	 * @return mixed[]|null
	 */
	public function array(mixed $value, bool $preserveKeys): ?array;

	/**
	 * If true, Object mappers will only accept the exact object instance match
	 * If false, Object mappers will try to convert scalars/arrays to objects
	 *
	 * @param class-string $className
	 */
	public function isStrictForObject(string $className): bool;

}
