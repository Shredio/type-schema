<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion;

use Shredio\TypeSchema\Conversion\Converter\Array\ArrayConverter;
use Shredio\TypeSchema\Conversion\Converter\Bool\BoolConverter;
use Shredio\TypeSchema\Conversion\Converter\Null\NullConverter;
use Shredio\TypeSchema\Conversion\Converter\Number\NumberConverter;
use Shredio\TypeSchema\Conversion\Converter\String\StringConverter;
use Shredio\TypeSchema\Conversion\Object\ObjectSupervisor;

final readonly class ConfigurableConversionStrategy implements ConversionStrategy
{

	public function __construct(
		private StringConverter $stringConverter,
		private NumberConverter $numberConverter,
		private BoolConverter $boolConverter,
		private NullConverter $nullConverter,
		private ArrayConverter $arrayConverter,
		private ObjectSupervisor $objectSupervisor,
	)
	{
	}

	public function string(mixed $value): ?string
	{
		return $this->stringConverter->string($value);
	}

	public function int(mixed $value): ?int
	{
		return $this->numberConverter->int($value);
	}

	public function float(mixed $value): ?float
	{
		return $this->numberConverter->float($value);
	}

	public function bool(mixed $value): ?bool
	{
		return $this->boolConverter->bool($value);
	}

	public function null(mixed $value): null|false
	{
		return $this->nullConverter->null($value);
	}

	public function array(mixed $value, bool $preserveKeys): ?array
	{
		return $this->arrayConverter->array($value, $preserveKeys);
	}

	/**
	 * @param class-string $className
	 */
	public function isStrictForObject(string $className): bool
	{
		return $this->objectSupervisor->isStrict($className);
	}

}
