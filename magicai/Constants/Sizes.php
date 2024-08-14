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
	const PORTRAIT  = '1024x1792';
	const LANDSCAPE = '1792x1024';
	const REGULAR   = '1024x1024';
	const MEDIUM    = '512x512';
	const SMALL     = '256x256';
}
