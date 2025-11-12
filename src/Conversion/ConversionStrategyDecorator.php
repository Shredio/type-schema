<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion;

abstract readonly class ConversionStrategyDecorator implements ConversionStrategy
{

	public function __construct(
		protected ConversionStrategy $inner,
	)
	{
	}

	public function string(mixed $value): ?string
	{
		return $this->inner->string($value);
	}

	public function int(mixed $value): ?int
	{
		return $this->inner->int($value);
	}

	public function float(mixed $value): ?float
	{
		return $this->inner->float($value);
	}

	public function bool(mixed $value): ?bool
	{
		return $this->inner->bool($value);
	}

	public function null(mixed $value): null|false
	{
		return $this->inner->null($value);
	}

	public function array(mixed $value, bool $preserveKeys): ?array
	{
		return $this->inner->array($value, $preserveKeys);
	}

	public function isStrictForObject(string $className): bool
	{
		return $this->inner->isStrictForObject($className);
	}

}
