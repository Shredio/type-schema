<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Converter\Array;

use Shredio\TypeSchema\Conversion\Converter\ConstructableConverter;
use Shredio\TypeSchema\Exception\LogicException;

final readonly class NeverArrayConverter implements ArrayConverter, ConstructableConverter
{

	/**
	 * @param non-empty-string $reason
	 */
	public function __construct(
		private string $reason,
	)
	{
	}

	public function array(mixed $value, bool $preserveKeys): ?array
	{
		throw new LogicException(sprintf('Cannot convert to array: %s', $this->reason));
	}

	public function constructorArguments(): array
	{
		return [$this->reason];
	}

}
