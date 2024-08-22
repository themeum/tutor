<?php
/**
 * Constants for keeping the OpenAI models
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\MagicAI\Constants;

/**
 * OpenAI image and chat completion model list.
 *
 * @since 3.0.0
 */
final class Models {
	/**
	 * The image generation model. dall-e-3 is the latest model by openai.
	 * This model could generate image of size 1024x1024, 1024x1792, 1792x1024.
	 * This model could generate only one image at a time.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const DALL_E_3 = 'dall-e-3';

	/**
	 * The image generation model. dall-e-2 is the legacy model by openai.
	 * This model could generate image of size 1024x1024, 512x512, and 256x256 sizes.
	 * This model could generate from minimum 1 upto 10 images at a time and editing images are allowed only by this model.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const DALL_E_2 = 'dall-e-2';

	/**
	 * Openai content generation or chat completion model for generating text content.
	 * gpt-4o is the latest model by openai.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const GPT_4O = 'gpt-4o';

	/**
	 * Openai content generation or chat completion model for generating text content.
	 * gpt-4o-mini is the minified version of the gpt-4o, cost efficient and trained by latest data.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const GPT_4O_MINI = 'gpt-4o-mini';

	/**
	 * Openai content generation or chat completion model for generating text content.
	 * gpt-3.5-turbo is the old model and only trained by the data upto september 2021.
	 *
	 * @var string
	 * @since 3.0.0
	 */
	const GPT_35_TURBO = 'gpt-3.5-turbo';
}
