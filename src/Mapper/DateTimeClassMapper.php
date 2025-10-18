<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Mapper\Options\DateTimeOptions;

/**
 * @template T of DateTime|DateTimeImmutable
 * @extends ClassMapper<T>
 */
final readonly class DateTimeClassMapper extends ClassMapper
{

	/**
	 * @param class-string<T> $className
	 */
	public function __construct(
		public string $className = DateTimeImmutable::class,
	)
	{
	}

	public function isSupported(string $className): bool
	{
		return is_a($className, DateTimeInterface::class, true);
	}

	public function create(string $className, mixed $valueToParse, TypeContext $context): object
	{
		if (is_string($valueToParse)) {
			$options = $context->getOption(DateTimeOptions::class) ?? new DateTimeOptions();

			foreach ($options->formats as $format) {
				$ret = $this->className::createFromFormat($format, $valueToParse, $options->timeZone);
				if ($ret !== false) {
					return $ret;
				}
			}

			if ($options->constructor) {
				$dateTime = date_create($valueToParse);
				if ($dateTime !== false) {
					return $this->className::createFromInterface($dateTime);
				}
			}
		}

		if (is_int($valueToParse) && $valueToParse > 0) {
			$options = $context->getOption(DateTimeOptions::class) ?? new DateTimeOptions();
			if ($options->allowIntAsTimestamp) {
				$dateTime = (new DateTime())->setTimestamp($valueToParse);
				return $this->className::createFromInterface($dateTime);
			}
		}

		return $context->errorElementFactory->invalidType($this->createDefinition($className), $valueToParse);
	}

}
