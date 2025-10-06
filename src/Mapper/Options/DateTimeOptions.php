<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Mapper\Options;

use DateTimeInterface;
use DateTimeZone;

final readonly class DateTimeOptions
{

	/**
	 * @param list<non-empty-string> $formats
	 */
	public function __construct(
		public array $formats = [DateTimeInterface::ATOM, 'Y-m-d H:i:s'],
		public bool $constructor = false,
		public bool $allowIntAsTimestamp = false,
		public ?DateTimeZone $timeZone = null,
	)
	{
	}

}
