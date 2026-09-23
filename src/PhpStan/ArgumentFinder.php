<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\PhpStan;

use PhpParser\Node\Arg;

final readonly class ArgumentFinder
{

	/**
	 * Finds an argument passed either by name or at the given position.
	 *
	 * @param array<Arg> $args
	 */
	public static function find(array $args, int $position, string $name): ?Arg
	{
		foreach ($args as $arg) {
			if ($arg->name !== null && $arg->name->toString() === $name) {
				return $arg;
			}
		}

		$arg = $args[$position] ?? null;
		if ($arg === null || $arg->name !== null || $arg->unpack) {
			return null;
		}

		return $arg;
	}

}
