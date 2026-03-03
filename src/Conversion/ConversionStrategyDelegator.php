<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion;

final readonly class ConversionStrategyDelegator implements ConversionStrategy
{

	public function __construct(
		private ConversionStrategy $string,
		private ConversionStrategy $int,
		private ConversionStrategy $float,
		private ConversionStrategy $bool,
		private ConversionStrategy $null,
		private ConversionStrategy $array,
		private ConversionStrategy $object,
	)
	{
	}

	public function string(mixed $value): ?string
	{
		return $this->string->string($value);
	}

	public function int(mixed $value): ?int
	{
		return $this->int->int($value);
	}

	public function float(mixed $value): ?float
	{
		return $this->float->float($value);
	}

	public function bool(mixed $value): ?bool
	{
		return $this->bool->bool($value);
	}

	public function null(mixed $value): null|false
	{
		return $this->null->null($value);
	}

	public function array(mixed $value, bool $preserveKeys): ?array
	{
		return $this->array->array($value, $preserveKeys);
	}

	public function isStrictForObject(string $className): bool
	{
		return $this->object->isStrictForObject($className);
	}

}
