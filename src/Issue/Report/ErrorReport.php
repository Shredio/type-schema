<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue\Report;

use Shredio\TypeSchema\Issue\CustomIssue;
use Shredio\TypeSchema\Issue\ErrorCategory;
use Shredio\TypeSchema\Issue\Issue;
use Shredio\TypeSchema\Issue\IssueNode;
use Shredio\TypeSchema\Issue\LocatedIssue;
use Shredio\TypeSchema\Issue\Path;
use Shredio\TypeSchema\Issue\Renderer\DeveloperValidationMessageFactory;
use Shredio\TypeSchema\Issue\Renderer\EnglishIssueRenderer;
use Shredio\TypeSchema\Issue\Renderer\IssueRenderer;
use Stringable;

final readonly class ErrorReport
{

	/**
	 * @param list<Path> $path
	 */
	public function __construct(
		public string|Stringable $message,
		public string|Stringable $messageForDeveloper,
		public array $path = [],
		public ErrorCategory $category = ErrorCategory::Validation,
		public ?Issue $issue = null,
	)
	{
	}

	/**
	 * @return non-empty-list<ErrorReport>
	 */
	public static function fromIssues(IssueNode $node, ?IssueRenderer $renderer = null, ?ErrorReportConfig $config = null): array
	{
		$renderer ??= new EnglishIssueRenderer();
		$config ??= new ErrorReportConfig();

		return array_map(
			static function (LocatedIssue $locatedIssue) use ($renderer, $config): self {
				$message = $renderer->render($locatedIssue->issue, $config);

				return new self(
					$message,
					self::getMessageForDeveloper($locatedIssue->issue, $message),
					$locatedIssue->path,
					$locatedIssue->issue->getCategory(),
					$locatedIssue->issue,
				);
			},
			$node->getIssues(),
		);
	}

	/**
	 * @return list<int|string>
	 */
	public function toArrayPath(): array
	{
		if ($this->path === []) {
			return [];
		}

		return array_map(
			fn (Path $path): int|string => $path->path,
			$this->path,
		);
	}

	public function toDebugPathString(): ?string
	{
		if ($this->path === []) {
			return null;
		}

		return implode('.', array_map(
			fn (Path $path): string => is_int($path->path) ? sprintf('[%d]', $path->path) : $this->escapeKey($path->path),
			$this->path
		));
	}

	public function toIdentifiedPath(string $separator = ' -> '): ?string
	{
		$parts = [];
		foreach ($this->path as $path) {
			if ($path->identified === null) {
				return null;
			}
			$parts[] = DeveloperValidationMessageFactory::describeValue($path->identified->value);
		}

		if ($parts === []) {
			return null;
		}
		
		return implode($separator, $parts);
	}

	/**
	 * A custom issue without a message of its own for developers shows them the message users get, as the renderer
	 * rendered it - a translatable one translated, not its untranslated source text.
	 */
	private static function getMessageForDeveloper(Issue $issue, string|Stringable $renderedMessage): string|Stringable
	{
		if ($issue instanceof CustomIssue && $issue->messageForDeveloper === null) {
			return $renderedMessage;
		}

		return $issue->getMessageForDeveloper();
	}

	private function escapeKey(string $key): string
	{
		if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
			return $key;
		}
		return sprintf("'%s'", str_replace("'", "\\'", $key));
	}

}
