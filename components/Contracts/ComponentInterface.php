<?php
/**
 * Tutor Component: ComponentInterface
 *
 * Defines the interface for all UI components.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface ComponentInterface
 *
 * Ensures that all components implement a render method.
 *
 * @since 4.0.0
 */
interface ComponentInterface {

	/**
	 * Make a component
	 *
	 * @since 4.0.0
	 *
	 * @return ComponentInterface
	 */
	public static function make(): ComponentInterface;

	/**
	 * Render the component output.
	 *
	 * All components must return their HTML as a string.
	 *
	 * @since 4.0.0
	 *
	 * @return string Component HTML output.
	 */
	public function render(): string;
}
