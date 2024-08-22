<?php
/**
 * Constants for keeping the image sizes
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\MagicAI\Constants;

/**
 * OpenAI allowed image sizes for the DALL-E-2 & 3 models.
 *
 * @since 3.0.0
 */
final class Sizes {
	/**
	 * The portrait mode size of the images. This size is only allowed by dall-e-3.
	 *
	 * @var string
	 * @since   3.0.0
	 */
	const PORTRAIT = '1024x1792';

	/**
	 * The landscape mode size of the images. The size is only allowed by dall-e-3.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const LANDSCAPE = '1792x1024';

	/**
	 * The regular (default) size for our system. This size is allowed by the both dall-e-2 & 3.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const REGULAR = '1024x1024';

	/**
	 * The medium size of the images. This size is only allowed by dall-e-2.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const MEDIUM = '512x512';

	/**
	 * The small size of the images. This size is only allowed by dall-e-2.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const SMALL = '256x256';
}
