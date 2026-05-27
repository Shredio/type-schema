<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Validation;

use Shredio\TypeSchema\Error\ErrorElement;
use Throwable;

interface ValidationFailed extends Throwable
{

	/**
	 * @return non-empty-list<ErrorElement[]>
	 */
	public function getErrors(): array;

}
