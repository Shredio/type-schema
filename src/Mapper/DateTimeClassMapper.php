<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Helper\NumberInclusiveRange;
use Shredio\TypeSchema\Issue\InvalidDate;
use Shredio\TypeSchema\Issue\InvalidType;
use Shredio\TypeSchema\Issue\NumberOutOfRange;
use Shredio\TypeSchema\Result\Failure;
use Shredio\TypeSchema\Mapper\Options\DateTimeOptions;

/**
 * Maps strings (and optionally integer timestamps) to DateTime, DateTimeImmutable and their subclasses.
 * DateTimeInterface is mapped to DateTimeImmutable.
 *
 * @extends ClassMapper<DateTimeInterface>
 */
final readonly class DateTimeClassMapper extends ClassMapper
{

	public function isSupported(string $className): bool
	{
		return $className === DateTimeInterface::class
			|| is_a($className, DateTime::class, true)
			|| is_a($className, DateTimeImmutable::class, true);
	}

	public function create(string $className, mixed $valueToParse, TypeContext $context): object
	{
		$options = $context->getOption(DateTimeOptions::class) ?? new DateTimeOptions();

		if (is_string($valueToParse)) {
			foreach ($options->formats as $format) {
				// Symfony throws MalformedDateTimeException in the createFromFormat method
				$dateTime = date_create_immutable_from_format($format, $valueToParse, $options->timeZone);
				// warnings mean an overflowing date or time (e.g. 2024-02-30), which PHP silently shifts
				if ($dateTime !== false && DateTimeImmutable::getLastErrors() === false) {
					return $this->createInstance($className, $dateTime);
				}
			}

			if ($options->constructor) {
				$dateTime = date_create($valueToParse);
				if ($dateTime !== false && DateTime::getLastErrors() === false) {
					return $this->createInstance($className, $dateTime);
				}
			}

			return new Failure(new InvalidDate($valueToParse));
		} else if (is_int($valueToParse)) {
			if (!$options->allowIntAsTimestamp) {
				return new Failure(new InvalidType($this->createNamedDefinition('string'), $valueToParse));
			}

			if ($valueToParse < 0) {
				$range = NumberInclusiveRange::fromInts(0);
				return new Failure(new NumberOutOfRange($valueToParse, $range, $range->decide($valueToParse)));
			}

			return $this->createInstance($className, (new DateTimeImmutable())->setTimestamp($valueToParse));
		}

		if ($options->allowIntAsTimestamp) {
			$def = $this->createUnionNamedDefinition('string', 'int');
		} else {
			$def = $this->createNamedDefinition('string');
		}

		return new Failure(new InvalidType($def, $valueToParse));
	}

	/**
	 * @param class-string<DateTimeInterface> $className
	 */
	private function createInstance(string $className, DateTimeInterface $dateTime): DateTimeInterface
	{
		if (is_a($className, DateTime::class, true)) {
			return $className::createFromInterface($dateTime);
		}

		if (is_a($className, DateTimeImmutable::class, true)) {
			return $className::createFromInterface($dateTime);
		}

		return DateTimeImmutable::createFromInterface($dateTime);
	}

}
