<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

final readonly class TypeSchemaErrorFormatter
{

	/**
	 * @return non-empty-string
	 */
	public static function prettyString(ErrorElement $error, string $listStyle = '✖ ', string $pathStyle = '→ '): string
	{
		$lines = [];

		foreach ($error->getReports() as $error) {
			$line = $listStyle . $error->messageForDeveloper;
			$pathString = $error->toDebugPathString();
			if ($pathString !== null) {
				$line .= sprintf("\n  %sat %s", $pathStyle, $pathString);
				$identifiedPath = $error->toIdentifiedPath();
				if ($identifiedPath !== null) {
					$line .= sprintf("\n  %sfor value %s", $pathStyle, $identifiedPath);
				}
			}
			$lines[] = $line;
		}

		return implode("\n", $lines);
	}

}
