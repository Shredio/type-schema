<?php declare(strict_types = 1);

namespace Shredio\TypeSchema\Enum;

enum ExtraKeysBehavior
{

	/**
	 * Reject extra keys.
	 *
	 * Throws an exception if the input contains keys
	 * that are not defined in the schema.
	 */
	case Reject;

	/**
	 * Accept extra keys.
	 *
	 * Allows keys that are not defined in the schema
	 * and keeps them in the resulting data.
	 */
	case Accept;

	/**
	 * Ignore extra keys.
	 *
	 * Allows keys that are not defined in the schema
	 * but removes them from the resulting data.
	 */
	case Ignore;


}
