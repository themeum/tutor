<?php
/**
 * Bootstrap Tutor LMS Plugin.
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map old namespace to new namespace.
 *
 * @since 4.2.0
 *
 * @return void
 */
$namespace_map = require __DIR__ . '/class-namespace-map.php';
spl_autoload_register(
	static function ( $class ) use ( $namespace_map ) {
		if ( ! is_array( $namespace_map ) || ! isset( $namespace_map[ $class ] ) ) {
			return;
		}

		$new_class = $namespace_map[ $class ];
		if ( ! class_exists( $new_class ) ) {
			return;
		}

		class_alias( $new_class, $class );
	},
	true,
	true
);

unset( $namespace_map );

require_once __DIR__ . '/../vendor/autoload.php';
