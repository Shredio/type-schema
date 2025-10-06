<?php declare(strict_types = 1);

namespace Tests\Common;

use Shredio\TypeSchema\Conversion\ConversionStrategy;
use Shredio\TypeSchema\Conversion\ConversionStrategyFactory;

final class TestConversionStrategy implements ConversionStrategy
{

	private ConversionStrategy $decorate;

	/** @var array<non-empty-string> */
	public array $called = [];

	public function __construct()
	{
		$this->decorate = ConversionStrategyFactory::strict();
	}

	public function string(mixed $value): ?string
	{
		$this->called[] = __FUNCTION__;
		return $this->decorate->string($value);
	}

	public function int(mixed $value): ?int
	{
		$this->called[] = __FUNCTION__;
		return $this->decorate->int($value);
	}

	public function float(mixed $value): ?float
	{
		$this->called[] = __FUNCTION__;
		return $this->decorate->float($value);
	}

	public function bool(mixed $value): ?bool
	{
		$this->called[] = __FUNCTION__;
		return $this->decorate->bool($value);
	}

	public function null(mixed $value): null|false
	{
		$this->called[] = __FUNCTION__;
		return $this->decorate->null($value);
	}

	public function array(mixed $value, bool $preserveKeys): ?array
	{
		$this->called[] = __FUNCTION__;
		return $this->decorate->array($value, $preserveKeys);
	}

	public function isStrictForObject(string $className): bool
	{
		return $this->decorate->isStrictForObject($className);
	}

}
