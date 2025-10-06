<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Shredio\TypeSchema\Context\TypeContext;
use Shredio\TypeSchema\Mapper\Options\DateTimeOptions;
use Shredio\TypeSchema\Types\Type;

/**
 * @template T of DateTime|DateTimeImmutable
 * @extends Type<T>
 */
final readonly class DateTimeMapper extends Type
{

	/**
	 * @param class-string<T> $className
	 */
	public function __construct(
		public string $className = DateTimeImmutable::class,
	)
	{
	}

	public function parse(mixed $valueToParse, TypeContext $context): mixed
	{
		if ($valueToParse instanceof $this->className) {
			return $valueToParse;
		}
		if ($valueToParse instanceof DateTimeInterface) {
			return $this->className::createFromInterface($valueToParse);
		}

		if ($context->conversionStrategy->isStrictForObject(DateTimeInterface::class)) {
			return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
		}

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

		return $context->errorElementFactory->invalidType($this->createDefinition($context), $valueToParse);
	}

	protected function getTypeNode(TypeContext $context): TypeNode
	{
		return new IdentifierTypeNode($this->className);
	}

}
