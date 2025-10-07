<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

final readonly class ErrorPath implements ErrorElement
{

	public function __construct(
		public ErrorElement $error,
		public Path $path,
	)
	{
	}

	public function getReports(array $path = []): array
	{
		$path[] = $this->path;
		return $this->error->getReports($path);
	}

}
