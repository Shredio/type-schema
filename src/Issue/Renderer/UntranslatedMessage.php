<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Issue\Renderer;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;

/**
 * Renders a translatable message without a catalogue - the message itself with its parameters substituted -
 * wherever no real translator is at hand (the English renderer, messages for developers). The message is the
 * English source text, so its plural forms are chosen by English rules whatever the process locale is; the
 * rules of e.g. Czech expect three forms and fail on the two an English message has.
 *
 * @internal
 */
final class UntranslatedMessage
{

	private const string SourceLocale = 'en';

	private static ?TranslatorInterface $identityTranslator = null;

	public static function render(TranslatableInterface $message): string
	{
		self::$identityTranslator ??= new class implements TranslatorInterface {

			use TranslatorTrait;

		};

		return $message->trans(self::$identityTranslator, self::SourceLocale);
	}

}
