<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion;

use Shredio\TypeSchema\Conversion\Converter\Array\ArrayConverter;
use Shredio\TypeSchema\Conversion\Converter\Array\NeverArrayConverter;
use Shredio\TypeSchema\Conversion\Converter\Bool\BoolConverter;
use Shredio\TypeSchema\Conversion\Converter\Bool\NeverBoolConverter;
use Shredio\TypeSchema\Conversion\Converter\Null\NeverNullConverter;
use Shredio\TypeSchema\Conversion\Converter\Null\NullConverter;
use Shredio\TypeSchema\Conversion\Converter\Number\NeverNumberConverter;
use Shredio\TypeSchema\Conversion\Converter\Number\NumberConverter;
use Shredio\TypeSchema\Conversion\Converter\String\NeverStringConverter;
use Shredio\TypeSchema\Conversion\Converter\String\StringConverter;
use Shredio\TypeSchema\Conversion\Object\NeverObjectSupervisor;
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

	/**
	 * @param non-empty-string|null $reason
	 */
	public static function forString(StringConverter $converter, ?string $reason = null): self
	{
		$reason ??= 'string-only strategy';

		return new self(
			stringConverter: $converter,
			numberConverter: new NeverNumberConverter($reason),
			boolConverter: new NeverBoolConverter($reason),
			nullConverter: new NeverNullConverter($reason),
			arrayConverter: new NeverArrayConverter($reason),
			objectSupervisor: new NeverObjectSupervisor($reason),
		);
	}

	/**
	 * @param non-empty-string|null $reason
	 */
	public static function forNumber(NumberConverter $converter, ?string $reason = null): self
	{
		$reason ??= 'number-only strategy';

		return new self(
			stringConverter: new NeverStringConverter($reason),
			numberConverter: $converter,
			boolConverter: new NeverBoolConverter($reason),
			nullConverter: new NeverNullConverter($reason),
			arrayConverter: new NeverArrayConverter($reason),
			objectSupervisor: new NeverObjectSupervisor($reason),
		);
	}

	/**
	 * @param non-empty-string|null $reason
	 */
	public static function forBool(BoolConverter $converter, ?string $reason = null): self
	{
		$reason ??= 'bool-only strategy';

		return new self(
			stringConverter: new NeverStringConverter($reason),
			numberConverter: new NeverNumberConverter($reason),
			boolConverter: $converter,
			nullConverter: new NeverNullConverter($reason),
			arrayConverter: new NeverArrayConverter($reason),
			objectSupervisor: new NeverObjectSupervisor($reason),
		);
	}

	/**
	 * @param non-empty-string|null $reason
	 */
	public static function forNull(NullConverter $converter, ?string $reason = null): self
	{
		$reason ??= 'null-only strategy';

		return new self(
			stringConverter: new NeverStringConverter($reason),
			numberConverter: new NeverNumberConverter($reason),
			boolConverter: new NeverBoolConverter($reason),
			nullConverter: $converter,
			arrayConverter: new NeverArrayConverter($reason),
			objectSupervisor: new NeverObjectSupervisor($reason),
		);
	}

	/**
	 * @param non-empty-string|null $reason
	 */
	public static function forArray(ArrayConverter $converter, ?string $reason = null): self
	{
		$reason ??= 'array-only strategy';

		return new self(
			stringConverter: new NeverStringConverter($reason),
			numberConverter: new NeverNumberConverter($reason),
			boolConverter: new NeverBoolConverter($reason),
			nullConverter: new NeverNullConverter($reason),
			arrayConverter: $converter,
			objectSupervisor: new NeverObjectSupervisor($reason),
		);
	}

	/**
	 * @param non-empty-string|null $reason
	 */
	public static function forObject(ObjectSupervisor $supervisor, ?string $reason = null): self
	{
		$reason ??= 'object-only strategy';

		return new self(
			stringConverter: new NeverStringConverter($reason),
			numberConverter: new NeverNumberConverter($reason),
			boolConverter: new NeverBoolConverter($reason),
			nullConverter: new NeverNullConverter($reason),
			arrayConverter: new NeverArrayConverter($reason),
			objectSupervisor: $supervisor,
		);
	}

}
