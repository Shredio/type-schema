<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

final readonly class ErrorReportConfig
{

	public function __construct(
		public bool $exposeExpectedType = true,
	)
	{
	}

}
