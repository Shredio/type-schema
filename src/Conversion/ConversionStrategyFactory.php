<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion;

use Shredio\TypeSchema\Conversion\Converter\Array\LenientArrayConverter;
use Shredio\TypeSchema\Conversion\Converter\Array\StrictArrayConverter;
use Shredio\TypeSchema\Conversion\Converter\Bool\LenientBoolConverter;
use Shredio\TypeSchema\Conversion\Converter\Bool\StrictBoolConverter;
use Shredio\TypeSchema\Conversion\Converter\Bool\StringBoolConverter;
use Shredio\TypeSchema\Conversion\Converter\Null\LenientNullConverter;
use Shredio\TypeSchema\Conversion\Converter\Null\StrictNullConverter;
use Shredio\TypeSchema\Conversion\Converter\Number\JsonNumberConverter;
use Shredio\TypeSchema\Conversion\Converter\Number\LenientNumberConverter;
use Shredio\TypeSchema\Conversion\Converter\Number\StrictNumberConverter;
use Shredio\TypeSchema\Conversion\Converter\String\LenientStringConverter;
use Shredio\TypeSchema\Conversion\Converter\String\StrictStringConverter;
use Shredio\TypeSchema\Conversion\Object\LenientObjectSupervisor;
use Shredio\TypeSchema\Conversion\Object\StrictObjectSupervisor;

final class ConversionStrategyFactory
{

	private static ?ConfigurableConversionStrategy $strictStrategy = null;
	private static ?ConfigurableConversionStrategy $lenientStrategy = null;
	private static ?ConfigurableConversionStrategy $jsonStrategy = null;
	private static ?ConfigurableConversionStrategy $csvStrategy = null;

	public static function strict(): ConversionStrategy
	{
		return self::$strictStrategy ??= new ConfigurableConversionStrategy(
			new StrictStringConverter(),
			new StrictNumberConverter(),
			new StrictBoolConverter(),
			new StrictNullConverter(),
			new StrictArrayConverter(),
			new StrictObjectSupervisor(),
		);
	}

	public static function lenient(): ConversionStrategy
	{
		return self::$lenientStrategy ??= new ConfigurableConversionStrategy(
			new LenientStringConverter(),
			new LenientNumberConverter(),
			new LenientBoolConverter(),
			new LenientNullConverter(),
			new LenientArrayConverter(),
			new LenientObjectSupervisor(),
		);
	}

	public static function json(): ConversionStrategy
	{
		return self::$jsonStrategy ??= new ConfigurableConversionStrategy(
			new StrictStringConverter(),
			new JsonNumberConverter(),
			new StrictBoolConverter(),
			new StrictNullConverter(),
			new LenientArrayConverter(),
			new LenientObjectSupervisor(),
		);
	}

	public static function csv(): ConversionStrategy
	{
		return self::$csvStrategy ??= new ConfigurableConversionStrategy(
			new StrictStringConverter(),
			new LenientNumberConverter(),
			new StringBoolConverter(),
			new LenientNullConverter(),
			new LenientArrayConverter(),
			new LenientObjectSupervisor(),
		);
	}

	public static function httpGet(): ConversionStrategy
	{
		return self::csv();
	}

}
