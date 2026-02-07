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

	public function withPath(Path $path): self
	{
		return new ErrorPath(
			$this->error,
			$path,
		);
	}

	public function getReports(array $path = [], ?ErrorReportConfig $config = null): array
	{
		$path[] = $this->path;
		return $this->error->getReports($path, $config);
	}

}
