<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Conversion\Object;

use Shredio\TypeSchema\Exception\LogicException;

final readonly class NeverObjectSupervisor implements ObjectSupervisor
{

	/**
	 * @param non-empty-string $reason
	 */
	public function __construct(
		private string $reason,
	)
	{
	}

	public function isStrict(string $className): bool
	{
		throw new LogicException(sprintf('Cannot check object strictness: %s', $this->reason));
	}

}
