<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Result;

use Shredio\TypeSchema\Issue\IssueNode;

/**
 * Parsed value that comes with notices. Returned from Type::parse() instead of the bare value only when notices exist.
 *
 * @template-covariant T
 */
final readonly class WithNotices
{

	/**
	 * @param T $value
	 */
	public function __construct(
		public mixed $value,
		public IssueNode $notices,
	)
	{
	}

	/**
	 * @template TNew
	 * @param TNew $value
	 * @return WithNotices<TNew>
	 */
	public function withValue(mixed $value): self
	{
		return new self($value, $this->notices);
	}

	/**
	 * @return WithNotices<T>
	 */
	public function withNotices(IssueNode $notices): self
	{
		return new self($this->value, $notices);
	}

}
