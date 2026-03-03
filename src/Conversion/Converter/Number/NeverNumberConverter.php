<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Number;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;
use Shredio\TypeSchema\Exception\LogicException;

final readonly class NeverNumberConverter implements NumberConverter, ConstructableConverter
{

	/**
	 * @param non-empty-string $reason
	 */
	public function __construct(
		private string $reason,
	)
	{
	}

	public function int(mixed $value): ?int
	{
		throw new LogicException(sprintf('Cannot convert to int: %s', $this->reason));
	}

	public function float(mixed $value): ?float
	{
		throw new LogicException(sprintf('Cannot convert to float: %s', $this->reason));
	}

	public function constructorArguments(): array
	{
		return [$this->reason];
	}

}
