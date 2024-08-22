<?php
/**
 * Helper class for handling magic ai functionalities
 *
 * @package Tutor\MagicAI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

/**
 * The base uri generation class.
 *
 * @since 3.0.0
 */
final class BaseUri {
	/**
	 * The uri string.
	 *
	 * @var string|null
	 */
	private $uri = null;

	/**
	 * The BaseUri constructor function.
	 *
	 * @param string $uri The uri string.
	 * @since 3.0.0
	 */
	private function __construct( string $uri ) {
		$this->uri = trim( $uri );
	}

	/**
	 * Create the base uri form the provided uri string.
	 *
	 * @param string $uri The uri string.
	 * @return string
	 * @since 3.0.0
	 */
	public static function from( string $uri ) {
		$uri = trim( trim( $uri ), '/' );

		return ( new self( $uri ) )->to_string();
	}

	/**
	 * Prepare the base uri for the request.
	 *
	 * @return string
	 * @since 3.0.0
	 */
	private function to_string() {
		foreach ( array( 'http://', 'https://' ) as $protocol ) {
			if ( str_starts_with( $this->uri, $protocol ) ) {
				return "{$this->uri}/";
			}
		}

		return "https://{$this->uri}/";
	}
}
