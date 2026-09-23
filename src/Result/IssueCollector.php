<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Result;

use Shredio\TypeSchema\Exception\LogicException;
use Shredio\TypeSchema\Issue\IdentifiedPath;
use Shredio\TypeSchema\Issue\Issue;
use Shredio\TypeSchema\Issue\IssueCollection;
use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Issue\IssuePath;
use Shredio\TypeSchema\Issue\Path;

/**
 * Accumulates errors and notices of child values in composite types. Create it lazily, only when the first issue occurs.
 */
final class IssueCollector
{

	/** @var list<IssueNode> */
	private array $errors = [];

	/** @var list<IssueNode> */
	private array $notices = [];

	/**
	 * @param Failure|WithNotices<mixed> $result
	 */
	public function addChild(Failure|WithNotices $result, string|int $key, ?IdentifiedPath $identified = null): void
	{
		$path = new Path($key, $identified);
		if ($result instanceof Failure) {
			$this->errors[] = new IssuePath($result->errors, $path);
			if ($result->notices !== null) {
				$this->notices[] = new IssuePath($result->notices, $path);
			}

			return;
		}

		$this->notices[] = new IssuePath($result->notices, $path);
	}

	public function addError(Issue $issue, string|int $key, ?IdentifiedPath $identified = null): void
	{
		$this->errors[] = new IssuePath($issue, new Path($key, $identified));
	}

	public function addNotice(Issue $issue, string|int $key, ?IdentifiedPath $identified = null): void
	{
		$this->notices[] = new IssuePath($issue, new Path($key, $identified));
	}

	public function hasErrors(): bool
	{
		return $this->errors !== [];
	}

	public function createFailure(): Failure
	{
		if ($this->errors === []) {
			throw new LogicException('Cannot create a failure without errors.');
		}

		return new Failure(
			IssueCollection::create($this->errors),
			$this->notices === [] ? null : IssueCollection::create($this->notices),
		);
	}

	/**
	 * @template TParsed
	 * @param TParsed $value
	 * @return TParsed|Failure|WithNotices<TParsed>
	 */
	public function createResult(mixed $value): mixed
	{
		if ($this->errors !== []) {
			return $this->createFailure();
		}

		if ($this->notices !== []) {
			return new WithNotices($value, IssueCollection::create($this->notices));
		}

		return $value;
	}

}
