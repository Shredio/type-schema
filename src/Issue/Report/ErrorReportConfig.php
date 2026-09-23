<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue\Report;

final readonly class ErrorReportConfig
{

	public function __construct(
		public bool $exposeExpectedType = true,
		public string $typeSeparator = '|',
	)
	{
	}

}
