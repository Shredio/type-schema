<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Validation;

use Shredio\TypeSchema\Error\ErrorCategory;
use Shredio\TypeSchema\Error\ErrorCollection;
use Shredio\TypeSchema\Error\ErrorElement;
use Shredio\TypeSchema\Error\ErrorMessage;
use Stringable;

trait CommonErrorElements
{

	public function createError(
		string|Stringable $message,
		string|Stringable|null $messageForDeveloper = null,
		ErrorCategory $category = ErrorCategory::Validation,
	): ErrorElement
	{
		return new ErrorMessage($message, $messageForDeveloper ?? $message, $category);
	}

	/**
	 * @param non-empty-list<ErrorElement> $elements
	 */
	public function createCollection(array $elements): ErrorElement
	{
		return new ErrorCollection($elements);
	}

}
