<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

use Shredio\TypeSchema\Validation\DeveloperValidationMessageFactory;
use Stringable;

final readonly class ErrorReport
{

	/**
	 * @param list<Path> $path
	 */
	public function __construct(
		public string|Stringable $message,
		public string|Stringable $messageForDeveloper,
		public array $path = [],
	)
	{
	}

	public function toPathString(): ?string
	{
		if ($this->path === []) {
			return null;
		}

		return implode('.', array_map(
			fn (Path $path): string => is_int($path->path) ? sprintf('[%d]', $path->path) : $this->escapeKey($path->path),
			$this->path
		));
	}

	public function toIdentifiedPath(string $separator = ' -> '): ?string
	{
		$parts = [];
		foreach ($this->path as $path) {
			if ($path->identified === null) {
				return null;
			}
			$parts[] = DeveloperValidationMessageFactory::describeValue($path->identified->value);
		}

		if ($parts === []) {
			return null;
		}
		
		return implode($separator, $parts);
	}

	private function escapeKey(string $key): string
	{
		if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
			return $key;
		}
		return sprintf("'%s'", str_replace("'", "\\'", $key));
	}

}
