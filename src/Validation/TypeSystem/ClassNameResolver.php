<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Validation\TypeSystem;

use ParseError;
use PhpToken;
use ReflectionClass;
use Shredio\TypeSchema\Exception\LogicException;

/**
 * Resolves a class name written in PHPDoc to a fully qualified name, using the namespace and the use statements
 * of the file the context class is declared in.
 *
 * @internal
 */
final class ClassNameResolver
{

	private const array BuiltinTypes = [
		'string' => true,
		'int' => true,
		'float' => true,
		'bool' => true,
		'array' => true,
		'object' => true,
		'callable' => true,
		'iterable' => true,
		'void' => true,
		'null' => true,
		'mixed' => true,
		'false' => true,
		'never' => true,
		'true' => true,
	];

	private const array NameTokens = [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED];

	private const array IgnoredTokens = [T_DOC_COMMENT, T_WHITESPACE, T_COMMENT];

	/** @var array<string, array<string, string>> class name => [alias => class name] */
	private static array $useStatementsByClass = [];

	/**
	 * @param ReflectionClass<object> $context
	 */
	public static function resolve(string $name, ReflectionClass $context): string
	{
		if ($name === '') {
			throw new LogicException('Class name must not be empty.');
		}

		$lowerName = strtolower($name);
		if (isset(self::BuiltinTypes[$lowerName])) {
			return $lowerName;
		}

		if ($lowerName === 'self' || $lowerName === 'static') {
			return $context->name;
		}

		if ($lowerName === 'parent') {
			$parentClass = $context->getParentClass();

			return $parentClass === false ? 'parent' : $parentClass->name;
		}

		if (str_starts_with($name, '\\')) {
			return ltrim($name, '\\');
		}

		$useStatements = self::getUseStatements($context);
		$parts = explode('\\', $name, 2);
		if (isset($useStatements[$parts[0]])) {
			$parts[0] = $useStatements[$parts[0]];

			return implode('\\', $parts);
		}

		if ($context->inNamespace()) {
			return sprintf('%s\\%s', $context->getNamespaceName(), $name);
		}

		return $name;
	}

	/**
	 * @param ReflectionClass<object> $class
	 * @return array<string, string> alias => class name
	 */
	private static function getUseStatements(ReflectionClass $class): array
	{
		if ($class->isAnonymous()) {
			throw new LogicException('Anonymous classes are not supported.');
		}

		$className = $class->name;
		if (!isset(self::$useStatementsByClass[$className])) {
			$fileName = $class->getFileName();
			if ($class->isInternal() || $fileName === false || !is_file($fileName)) {
				self::$useStatementsByClass[$className] = [];
			} else {
				$code = file_get_contents($fileName);
				self::$useStatementsByClass = ($code === false ? [] : self::parseUseStatements($code, $className))
					+ self::$useStatementsByClass
					+ [$className => []];
			}
		}

		return self::$useStatementsByClass[$className];
	}

	/**
	 * Parses use statements of all classes declared in the code up to the given class.
	 *
	 * @return array<string, array<string, string>> class name => [alias => class name]
	 */
	private static function parseUseStatements(string $code, string $forClass): array
	{
		try {
			$tokens = array_values(PhpToken::tokenize($code, TOKEN_PARSE));
		} catch (ParseError) { // @phpstan-ignore catch.neverThrown (TOKEN_PARSE throws for invalid code, e.g. a file changed after it was loaded)
			return [];
		}

		$namespace = '';
		$class = null;
		$classLevel = 0;
		$level = 0;
		$result = [];
		$uses = [];
		$position = 0;

		while (isset($tokens[$position])) {
			$token = $tokens[$position];
			$position++;

			switch ($token->id) {
				case T_NAMESPACE:
					$namespace = ltrim(sprintf('%s\\', self::fetch($tokens, $position, self::NameTokens)), '\\');
					$uses = [];
					break;

				case T_CLASS:
				case T_INTERFACE:
				case T_TRAIT:
				case T_ENUM:
					$name = self::fetch($tokens, $position, T_STRING);
					if ($name !== null) {
						$class = sprintf('%s%s', $namespace, $name);
						$classLevel = $level + 1;
						$result[$class] = $uses;
						if ($class === $forClass) {
							return $result;
						}
					}

					break;

				case T_USE:
					while ($class === null && ($name = self::fetch($tokens, $position, self::NameTokens)) !== null) {
						$name = ltrim($name, '\\');
						if (self::fetch($tokens, $position, '{') !== null) {
							while (($suffix = self::fetch($tokens, $position, self::NameTokens)) !== null) {
								if (self::fetch($tokens, $position, T_AS) !== null) {
									$uses[(string) self::fetch($tokens, $position, T_STRING)] = sprintf('%s%s', $name, $suffix);
								} else {
									$uses[self::lastNamePart($suffix)] = sprintf('%s%s', $name, $suffix);
								}

								if (self::fetch($tokens, $position, ',') === null) {
									break;
								}
							}
						} elseif (self::fetch($tokens, $position, T_AS) !== null) {
							$uses[(string) self::fetch($tokens, $position, T_STRING)] = $name;
						} else {
							$uses[self::lastNamePart($name)] = $name;
						}

						if (self::fetch($tokens, $position, ',') === null) {
							break;
						}
					}

					break;

				case T_CURLY_OPEN:
				case T_DOLLAR_OPEN_CURLY_BRACES:
				case ord('{'):
					$level++;
					break;

				case ord('}'):
					if ($level === $classLevel) {
						$class = null;
						$classLevel = 0;
					}

					$level--;
					break;
			}
		}

		return $result;
	}

	/**
	 * Concatenates the following tokens of the given kind, skipping whitespace and comments.
	 *
	 * @param list<PhpToken> $tokens
	 * @param int|string|list<int|string> $kind
	 */
	private static function fetch(array $tokens, int &$position, int|string|array $kind): ?string
	{
		$result = null;
		while (isset($tokens[$position])) {
			$token = $tokens[$position];
			if ($token->is($kind)) {
				$result = sprintf('%s%s', $result, $token->text);
			} elseif (!$token->is(self::IgnoredTokens)) {
				break;
			}

			$position++;
		}

		return $result;
	}

	private static function lastNamePart(string $name): string
	{
		$position = strrpos($name, '\\');

		return $position === false ? $name : substr($name, $position + 1);
	}

}
