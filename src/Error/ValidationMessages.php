<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Error;

use Symfony\Component\Translation\TranslatableMessage;

final readonly class ValidationMessages
{

	private const string Domain = 'validators';

	public static function maxStrLength(int $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.',
			['{{ limit }}' => $limit],
			'validators',
		);
	}

	public static function mustNotBeBlank(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should not be blank.',
			[],
			self::Domain,
		);
	}

	public static function mustBeBlank(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be blank.',
			[],
			self::Domain,
		);
	}

	public static function mustBeTypeOf(string $type): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be of type {{ type }}.',
			['{{ type }}' => $type],
			self::Domain,
		);
	}

	public static function mustBeFalse(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be false.',
			[],
			self::Domain,
		);
	}

	public static function mustBeTrue(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be true.',
			[],
			self::Domain,
		);
	}

	public static function invalidChoice(): TranslatableMessage
	{
		return new TranslatableMessage(
			'The value you selected is not a valid choice.',
			[],
			self::Domain,
		);
	}

	public static function minChoicesRequired(int $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'You must select at least {{ limit }} choice.|You must select at least {{ limit }} choices.',
			['{{ limit }}' => $limit],
			self::Domain,
		);
	}

	public static function maxChoicesAllowed(int $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'You must select at most {{ limit }} choice.|You must select at most {{ limit }} choices.',
			['{{ limit }}' => $limit],
			self::Domain,
		);
	}

	public static function oneOrMoreValuesInvalid(): TranslatableMessage
	{
		return new TranslatableMessage(
			'One or more of the given values is invalid.',
			[],
			self::Domain,
		);
	}

	public static function fieldNotExpected(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This field was not expected.',
			[],
			self::Domain,
		);
	}

	public static function fieldMissing(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This field is missing.',
			[],
			self::Domain,
		);
	}

	public static function invalidDate(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid date.',
			[],
			self::Domain,
		);
	}

	public static function invalidDateTime(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid datetime.',
			[],
			self::Domain,
		);
	}

	public static function invalidEmail(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid email address.',
			[],
			self::Domain,
		);
	}

	public static function fileNotFound(): TranslatableMessage
	{
		return new TranslatableMessage(
			'The file could not be found.',
			[],
			self::Domain,
		);
	}

	public static function fileNotReadable(): TranslatableMessage
	{
		return new TranslatableMessage(
			'The file is not readable.',
			[],
			self::Domain,
		);
	}

	public static function fileTooLargeWithSize(string $size, string $suffix, string $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.',
			['{{ size }}' => $size, '{{ suffix }}' => $suffix, '{{ limit }}' => $limit],
			self::Domain,
		);
	}

	public static function invalidMimeType(string $type, string $types): TranslatableMessage
	{
		return new TranslatableMessage(
			'The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}.',
			['{{ type }}' => $type, '{{ types }}' => $types],
			self::Domain,
		);
	}

	public static function valueTooHigh(int|float $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be {{ limit }} or less.',
			['{{ limit }}' => $limit],
			self::Domain,
		);
	}

	public static function minStrLength(int $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more.',
			['{{ limit }}' => $limit],
			self::Domain,
		);
	}

	public static function valueTooLow(int|float $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be {{ limit }} or more.',
			['{{ limit }}' => $limit],
			self::Domain,
		);
	}

	public static function mustNotBeNull(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should not be null.',
			[],
			self::Domain,
		);
	}

	public static function mustBeNull(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be null.',
			[],
			self::Domain,
		);
	}

	public static function invalidValue(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not valid.',
			[],
			self::Domain,
		);
	}

	public static function invalidTime(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid time.',
			[],
			self::Domain,
		);
	}

	public static function invalidUrl(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid URL.',
			[],
			self::Domain,
		);
	}

	public static function valuesMustBeEqual(): TranslatableMessage
	{
		return new TranslatableMessage(
			'The two values should be equal.',
			[],
			self::Domain,
		);
	}

	public static function fileTooLarge(string $limit, string $suffix): TranslatableMessage
	{
		return new TranslatableMessage(
			'The file is too large. Allowed maximum size is {{ limit }} {{ suffix }}.',
			['{{ limit }}' => $limit, '{{ suffix }}' => $suffix],
			self::Domain,
		);
	}

	public static function fileTooLargeGeneric(): TranslatableMessage
	{
		return new TranslatableMessage(
			'The file is too large.',
			[],
			self::Domain,
		);
	}

	public static function fileUploadFailed(): TranslatableMessage
	{
		return new TranslatableMessage(
			'The file could not be uploaded.',
			[],
			self::Domain,
		);
	}

	public static function invalidNumber(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be a valid number.',
			[],
			self::Domain,
		);
	}

	public static function invalidImage(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This file is not a valid image.',
			[],
			self::Domain,
		);
	}

	public static function invalidIpAddress(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid IP address.',
			[],
			self::Domain,
		);
	}

	public static function invalidLanguage(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid language.',
			[],
			self::Domain,
		);
	}

	public static function invalidLocale(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid locale.',
			[],
			self::Domain,
		);
	}

	public static function invalidCountry(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid country.',
			[],
			self::Domain,
		);
	}

	public static function valueAlreadyUsed(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is already used.',
			[],
			self::Domain,
		);
	}

	public static function imageSizeNotDetected(): TranslatableMessage
	{
		return new TranslatableMessage(
			'The size of the image could not be detected.',
			[],
			self::Domain,
		);
	}

	public static function imageWidthTooBig(int $width, int $maxWidth): TranslatableMessage
	{
		return new TranslatableMessage(
			'The image width is too big ({{ width }}px). Allowed maximum width is {{ max_width }}px.',
			['{{ width }}' => $width, '{{ max_width }}' => $maxWidth],
			self::Domain,
		);
	}

	public static function imageWidthTooSmall(int $width, int $minWidth): TranslatableMessage
	{
		return new TranslatableMessage(
			'The image width is too small ({{ width }}px). Minimum width expected is {{ min_width }}px.',
			['{{ width }}' => $width, '{{ min_width }}' => $minWidth],
			self::Domain,
		);
	}

	public static function imageHeightTooBig(int $height, int $maxHeight): TranslatableMessage
	{
		return new TranslatableMessage(
			'The image height is too big ({{ height }}px). Allowed maximum height is {{ max_height }}px.',
			['{{ height }}' => $height, '{{ max_height }}' => $maxHeight],
			self::Domain,
		);
	}

	public static function imageHeightTooSmall(int $height, int $minHeight): TranslatableMessage
	{
		return new TranslatableMessage(
			'The image height is too small ({{ height }}px). Minimum height expected is {{ min_height }}px.',
			['{{ height }}' => $height, '{{ min_height }}' => $minHeight],
			self::Domain,
		);
	}

	public static function mustBeCurrentPassword(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be the user\'s current password.',
			[],
			self::Domain,
		);
	}

	public static function exactStrLength(int $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should have exactly {{ limit }} character.|This value should have exactly {{ limit }} characters.',
			['{{ limit }}' => $limit],
			self::Domain,
		);
	}

	public static function filePartiallyUploaded(): TranslatableMessage
	{
		return new TranslatableMessage(
			'The file was only partially uploaded.',
			[],
			self::Domain,
		);
	}

	public static function noFileUploaded(): TranslatableMessage
	{
		return new TranslatableMessage(
			'No file was uploaded.',
			[],
			self::Domain,
		);
	}

	public static function noTempFolderConfigured(): TranslatableMessage
	{
		return new TranslatableMessage(
			'No temporary folder was configured in php.ini, or the configured folder does not exist.',
			[],
			self::Domain,
		);
	}

	public static function cannotWriteTempFile(): TranslatableMessage
	{
		return new TranslatableMessage(
			'Cannot write temporary file to disk.',
			[],
			self::Domain,
		);
	}

	public static function phpExtensionCausedUploadFailure(): TranslatableMessage
	{
		return new TranslatableMessage(
			'A PHP extension caused the upload to fail.',
			[],
			self::Domain,
		);
	}

	public static function collectionMinElements(int $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'This collection should contain {{ limit }} element or more.|This collection should contain {{ limit }} elements or more.',
			['{{ limit }}' => $limit],
			self::Domain,
		);
	}

	public static function collectionMaxElements(int $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'This collection should contain {{ limit }} element or less.|This collection should contain {{ limit }} elements or less.',
			['{{ limit }}' => $limit],
			self::Domain,
		);
	}

	public static function collectionExactElements(int $limit): TranslatableMessage
	{
		return new TranslatableMessage(
			'This collection should contain exactly {{ limit }} element.|This collection should contain exactly {{ limit }} elements.',
			['{{ limit }}' => $limit],
			self::Domain,
		);
	}

	public static function invalidCardNumber(): TranslatableMessage
	{
		return new TranslatableMessage(
			'Invalid card number.',
			[],
			self::Domain,
		);
	}

	public static function unsupportedCardType(): TranslatableMessage
	{
		return new TranslatableMessage(
			'Unsupported card type or invalid card number.',
			[],
			self::Domain,
		);
	}

	public static function invalidIban(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid International Bank Account Number (IBAN).',
			[],
			self::Domain,
		);
	}

	public static function invalidIsbn10(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid ISBN-10.',
			[],
			self::Domain,
		);
	}

	public static function invalidIsbn13(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid ISBN-13.',
			[],
			self::Domain,
		);
	}

	public static function invalidIsbn(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is neither a valid ISBN-10 nor a valid ISBN-13.',
			[],
			self::Domain,
		);
	}

	public static function invalidIssn(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid ISSN.',
			[],
			self::Domain,
		);
	}

	public static function invalidCurrency(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid currency.',
			[],
			self::Domain,
		);
	}

	public static function mustEqualValue(string $comparedValue): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be equal to {{ compared_value }}.',
			['{{ compared_value }}' => $comparedValue],
			self::Domain,
		);
	}

	public static function mustBeGreaterThan(int|float $comparedValue): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be greater than {{ compared_value }}.',
			['{{ compared_value }}' => $comparedValue],
			self::Domain,
		);
	}

	public static function mustBeGreaterThanOrEqual(int|float $comparedValue): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be greater than or equal to {{ compared_value }}.',
			['{{ compared_value }}' => $comparedValue],
			self::Domain,
		);
	}

	public static function mustBeIdenticalTo(string $comparedValueType, string $comparedValue): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be identical to {{ compared_value_type }} {{ compared_value }}.',
			['{{ compared_value_type }}' => $comparedValueType, '{{ compared_value }}' => $comparedValue],
			self::Domain,
		);
	}

	public static function mustBeLessThan(int|float $comparedValue): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be less than {{ compared_value }}.',
			['{{ compared_value }}' => $comparedValue],
			self::Domain,
		);
	}

	public static function mustBeLessThanOrEqual(int|float $comparedValue): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be less than or equal to {{ compared_value }}.',
			['{{ compared_value }}' => $comparedValue],
			self::Domain,
		);
	}

	public static function mustNotEqualValue(string $comparedValue): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should not be equal to {{ compared_value }}.',
			['{{ compared_value }}' => $comparedValue],
			self::Domain,
		);
	}

	public static function mustNotBeIdenticalTo(string $comparedValueType, string $comparedValue): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should not be identical to {{ compared_value_type }} {{ compared_value }}.',
			['{{ compared_value_type }}' => $comparedValueType, '{{ compared_value }}' => $comparedValue],
			self::Domain,
		);
	}

	public static function imageRatioTooBig(float $ratio, float $maxRatio): TranslatableMessage
	{
		return new TranslatableMessage(
			'The image ratio is too big ({{ ratio }}). Allowed maximum ratio is {{ max_ratio }}.',
			['{{ ratio }}' => $ratio, '{{ max_ratio }}' => $maxRatio],
			self::Domain,
		);
	}

	public static function imageRatioTooSmall(float $ratio, float $minRatio): TranslatableMessage
	{
		return new TranslatableMessage(
			'The image ratio is too small ({{ ratio }}). Minimum ratio expected is {{ min_ratio }}.',
			['{{ ratio }}' => $ratio, '{{ min_ratio }}' => $minRatio],
			self::Domain,
		);
	}

	public static function imageSquareNotAllowed(int $width, int $height): TranslatableMessage
	{
		return new TranslatableMessage(
			'The image is square ({{ width }}x{{ height }}px). Square images are not allowed.',
			['{{ width }}' => $width, '{{ height }}' => $height],
			self::Domain,
		);
	}

	public static function imageLandscapeNotAllowed(int $width, int $height): TranslatableMessage
	{
		return new TranslatableMessage(
			'The image is landscape oriented ({{ width }}x{{ height }}px). Landscape oriented images are not allowed.',
			['{{ width }}' => $width, '{{ height }}' => $height],
			self::Domain,
		);
	}

	public static function imagePortraitNotAllowed(int $width, int $height): TranslatableMessage
	{
		return new TranslatableMessage(
			'The image is portrait oriented ({{ width }}x{{ height }}px). Portrait oriented images are not allowed.',
			['{{ width }}' => $width, '{{ height }}' => $height],
			self::Domain,
		);
	}

	public static function emptyFileNotAllowed(): TranslatableMessage
	{
		return new TranslatableMessage(
			'An empty file is not allowed.',
			[],
			self::Domain,
		);
	}

	public static function hostNotResolved(): TranslatableMessage
	{
		return new TranslatableMessage(
			'The host could not be resolved.',
			[],
			self::Domain,
		);
	}

	public static function charsetMismatch(string $charset): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value does not match the expected {{ charset }} charset.',
			['{{ charset }}' => $charset],
			self::Domain,
		);
	}

	public static function invalidBic(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid Business Identifier Code (BIC).',
			[],
			self::Domain,
		);
	}

	public static function genericError(): TranslatableMessage
	{
		return new TranslatableMessage(
			'Error',
			[],
			self::Domain,
		);
	}

	public static function invalidUuid(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid UUID.',
			[],
			self::Domain,
		);
	}

	public static function mustBeMultipleOf(int|float $comparedValue): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be a multiple of {{ compared_value }}.',
			['{{ compared_value }}' => $comparedValue],
			self::Domain,
		);
	}

	public static function bicNotAssociatedWithIban(string $iban): TranslatableMessage
	{
		return new TranslatableMessage(
			'This Business Identifier Code (BIC) is not associated with IBAN {{ iban }}.',
			['{{ iban }}' => $iban],
			self::Domain,
		);
	}

	public static function invalidJson(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be valid JSON.',
			[],
			self::Domain,
		);
	}

	public static function collectionMustHaveUniqueElements(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This collection should contain only unique elements.',
			[],
			self::Domain,
		);
	}

	public static function mustBePositive(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be positive.',
			[],
			self::Domain,
		);
	}

	public static function mustBePositiveOrZero(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be either positive or zero.',
			[],
			self::Domain,
		);
	}

	public static function mustBeNegative(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be negative.',
			[],
			self::Domain,
		);
	}

	public static function mustBeNegativeOrZero(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be either negative or zero.',
			[],
			self::Domain,
		);
	}

	public static function invalidTimezone(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid timezone.',
			[],
			self::Domain,
		);
	}

	public static function passwordLeaked(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This password has been leaked in a data breach, it must not be used. Please use another password.',
			[],
			self::Domain,
		);
	}

	public static function mustBeBetween(int|float $min, int|float $max): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be between {{ min }} and {{ max }}.',
			['{{ min }}' => $min, '{{ max }}' => $max],
			self::Domain,
		);
	}

	public static function invalidHostname(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid hostname.',
			[],
			self::Domain,
		);
	}

	public static function collectionElementCountMustBeMultipleOf(int $comparedValue): TranslatableMessage
	{
		return new TranslatableMessage(
			'The number of elements in this collection should be a multiple of {{ compared_value }}.',
			['{{ compared_value }}' => $comparedValue],
			self::Domain,
		);
	}

	public static function mustSatisfyAtLeastOneConstraint(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should satisfy at least one of the following constraints:',
			[],
			self::Domain,
		);
	}

	public static function eachElementMustSatisfyConstraints(): TranslatableMessage
	{
		return new TranslatableMessage(
			'Each element of this collection should satisfy its own set of constraints.',
			[],
			self::Domain,
		);
	}

	public static function invalidIsin(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid International Securities Identification Number (ISIN).',
			[],
			self::Domain,
		);
	}

	public static function invalidExpression(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should be a valid expression.',
			[],
			self::Domain,
		);
	}

	public static function invalidCssColor(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid CSS color.',
			[],
			self::Domain,
		);
	}

	public static function invalidCidrNotation(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid CIDR notation.',
			[],
			self::Domain,
		);
	}

	public static function netmaskValueOutOfRange(int $min, int $max): TranslatableMessage
	{
		return new TranslatableMessage(
			'The value of the netmask should be between {{ min }} and {{ max }}.',
			['{{ min }}' => $min, '{{ max }}' => $max],
			self::Domain,
		);
	}

	public static function filenameTooLong(int $filenameMaxLength): TranslatableMessage
	{
		return new TranslatableMessage(
			'The filename is too long. It should have {{ filename_max_length }} character or less.|The filename is too long. It should have {{ filename_max_length }} characters or less.',
			['{{ filename_max_length }}' => $filenameMaxLength],
			self::Domain,
		);
	}

	public static function passwordStrengthTooLow(): TranslatableMessage
	{
		return new TranslatableMessage(
			'The password strength is too low. Please use a stronger password.',
			[],
			self::Domain,
		);
	}

	public static function charactersNotAllowedByRestrictionLevel(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value contains characters that are not allowed by the current restriction-level.',
			[],
			self::Domain,
		);
	}

	public static function invisibleCharactersNotAllowed(): TranslatableMessage
	{
		return new TranslatableMessage(
			'Using invisible characters is not allowed.',
			[],
			self::Domain,
		);
	}

	public static function mixingNumbersFromDifferentScriptsNotAllowed(): TranslatableMessage
	{
		return new TranslatableMessage(
			'Mixing numbers from different scripts is not allowed.',
			[],
			self::Domain,
		);
	}

	public static function hiddenOverlayCharactersNotAllowed(): TranslatableMessage
	{
		return new TranslatableMessage(
			'Using hidden overlay characters is not allowed.',
			[],
			self::Domain,
		);
	}

	public static function invalidFileExtension(string $extension, string $extensions): TranslatableMessage
	{
		return new TranslatableMessage(
			'The extension of the file is invalid ({{ extension }}). Allowed extensions are {{ extensions }}.',
			['{{ extension }}' => $extension, '{{ extensions }}' => $extensions],
			self::Domain,
		);
	}

	public static function invalidCharacterEncoding(string $detected, string $encodings): TranslatableMessage
	{
		return new TranslatableMessage(
			'The detected character encoding is invalid ({{ detected }}). Allowed encodings are {{ encodings }}.',
			['{{ detected }}' => $detected, '{{ encodings }}' => $encodings],
			self::Domain,
		);
	}

	public static function invalidMacAddress(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid MAC address.',
			[],
			self::Domain,
		);
	}

	public static function urlMissingTopLevelDomain(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This URL is missing a top-level domain.',
			[],
			self::Domain,
		);
	}

	public static function minWordCount(int $min): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is too short. It should contain at least one word.|This value is too short. It should contain at least {{ min }} words.',
			['{{ min }}' => $min],
			self::Domain,
		);
	}

	public static function maxWordCount(int $max): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is too long. It should contain one word.|This value is too long. It should contain {{ max }} words or less.',
			['{{ max }}' => $max],
			self::Domain,
		);
	}

	public static function invalidIso8601Week(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value does not represent a valid week in the ISO 8601 format.',
			[],
			self::Domain,
		);
	}

	public static function invalidWeek(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid week.',
			[],
			self::Domain,
		);
	}

	public static function weekTooEarly(string $min): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should not be before week "{{ min }}".',
			['{{ min }}' => $min],
			self::Domain,
		);
	}

	public static function weekTooLate(string $max): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value should not be after week "{{ max }}".',
			['{{ max }}' => $max],
			self::Domain,
		);
	}

	public static function invalidTwigTemplate(): TranslatableMessage
	{
		return new TranslatableMessage(
			'This value is not a valid Twig template.',
			[],
			self::Domain,
		);
	}

}
