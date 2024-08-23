<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\OpenAI\Constants;

/**
 * Class for keeping the content types.
 *
 * @since 3.0.0
 */
final class ContentTypes {
	/**
	 * The application/json content type.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const JSON = 'application/json';

	/**
	 * The multipart/form-data content type.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const MULTIPART = 'multipart/form-data';

	/**
	 * The text/plain content type
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const PLAIN_TEXT = 'text/plain';
}
