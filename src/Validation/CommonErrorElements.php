<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Validation;

use Shredio\TypeSchema\Error\ErrorCollection;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorMessage;
use Stringable;

trait CommonErrorElements
{

	public function createError(string|Stringable $message, string|Stringable|null $messageForDeveloper = null): ErrorElement
	{
		return new ErrorMessage($message, $messageForDeveloper ?? $message);
	}

	/**
	 * @param non-empty-list<ErrorElement> $elements
	 */
	public function createCollection(array $elements): ErrorElement
	{
		return new ErrorCollection($elements);
	}

}
