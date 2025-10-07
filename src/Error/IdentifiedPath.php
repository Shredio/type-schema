<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

final readonly class IdentifiedPath
{

	public function __construct(
		public mixed $value,
	)
	{
	}

	/**
	 * @param non-empty-string|null $identifier
	 * @param mixed[] $values
	 */
	public static function create(?string $identifier, array $values): ?self
	{
		if ($identifier === null) {
			return null;
		}
		if (!array_key_exists($identifier, $values)) {
			return null;
		}

		return new self($values[$identifier]);
	}

}
