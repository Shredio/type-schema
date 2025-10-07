<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

interface ErrorElement
{

	/**
	 * @param list<Path> $path
	 * @return non-empty-list<ErrorReport>
	 */
	public function getReports(array $path = []): array;

}
