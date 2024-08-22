<?php
/**
 * Main class to handle tutor native e-commerce.
 *
 * @package Tutor\Ecommerce
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\MagicAI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The base class for instantiating the controllers.
 * The controllers are responsible for generating text, image, course contents by using openai.
 *
 * @since 3.0.0
 */
class MagicAI {

	/**
	 * The constructor method for instantiating the AI Controllers.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		new ImageController();
		new TextController();
		new CourseGenerationController();
	}
}
