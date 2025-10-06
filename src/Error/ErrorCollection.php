<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

final readonly class ErrorCollection implements ErrorElement
{

	/**
	 * @param non-empty-list<ErrorElement> $collection
	 */
	public function __construct(
		public array $collection,
	)
	{
	}

	public function getReports(array $path = []): array
	{
		return array_merge(...array_map(
			fn (ErrorElement $element): array => $element->getReports($path),
			$this->collection,
		));
	}

}
