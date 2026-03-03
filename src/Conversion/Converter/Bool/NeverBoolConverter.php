<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Bool;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;
use Shredio\TypeSchema\Exception\LogicException;

final readonly class NeverBoolConverter implements BoolConverter, ConstructableConverter
{

	/**
	 * @param non-empty-string $reason
	 */
	public function __construct(
		private string $reason,
	)
	{
	}

	public function bool(mixed $value): ?bool
	{
		throw new LogicException(sprintf('Cannot convert to bool: %s', $this->reason));
	}

	public function constructorArguments(): array
	{
		return [$this->reason];
	}

}
