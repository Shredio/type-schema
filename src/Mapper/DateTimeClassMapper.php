<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Helper\NumberExclusiveRange;
use Shredio\TypeSchema\Mapper\Options\DateTimeOptions;

/**
 * @extends ClassMapper<DateTime|DateTimeImmutable>
 */
final readonly class DateTimeClassMapper extends ClassMapper
{

	public function isSupported(string $className): bool
	{
		return is_a($className, DateTimeInterface::class, true);
	}

	public function create(string $className, mixed $valueToParse, TypeContext $context): object
	{
		$options = $context->getOption(DateTimeOptions::class) ?? new DateTimeOptions();

		if (is_string($valueToParse)) {
			foreach ($options->formats as $format) {
				// Symfony throws MalformedDateTimeException in the createFromFormat method
				$ret = date_create_immutable_from_format($format, $valueToParse, $options->timeZone);
				if ($ret !== false) {
					return $className::createFromInterface($ret);
				}
			}

			if ($options->constructor) {
				$dateTime = date_create($valueToParse);
				if ($dateTime !== false) {
					return $className::createFromInterface($dateTime);
				}
			}

			return $context->errorElementFactory->invalidDate($valueToParse);
		} else if (is_int($valueToParse)) {
			if (!$options->allowIntAsTimestamp) {
				return $context->errorElementFactory->invalidType($this->createNamedDefinition('string'), $valueToParse);
			}

			if ($valueToParse < 0) {
				$range = NumberExclusiveRange::fromInts(0);
				return $context->errorElementFactory->numberRange(
					$this->createNamedDefinition('int'),
					$valueToParse,
					$range,
					$range->decide($valueToParse),
				);
			}

			$dateTime = (new DateTime())->setTimestamp($valueToParse);
			return $className::createFromInterface($dateTime);
		}

		if ($options->allowIntAsTimestamp) {
			$def = $this->createUnionNamedDefinition('string', 'int');
		} else {
			$def = $this->createNamedDefinition('string');
		}

		return $context->errorElementFactory->invalidType($def, $valueToParse);
	}

}
