<?php
namespace Ollyo\PaymentHub\Core\Support;

use Ollyo\PaymentHub\Exceptions\NotFoundException;

/**
 * Core helpers for the PaymentHub library.
 *
 * @since 1.0.0
 * @since 4.2.0 HTTP, money, address and order data helpers moved to
 *              \Tutor\PaymentGateways\Http and \Tutor\PaymentGateways\Utils.
 */
class System {
	/**
	 * Create an instance of the given class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class Fully qualified class name.
	 *
	 * @return object New instance of the class.
	 *
	 * @throws NotFoundException If the class does not exist.
	 */
	public static function createClassInstance( $class ) {
		if ( ! class_exists( $class ) ) {
			throw new NotFoundException( esc_html( sprintf( 'The class %s does not found!', $class ) ) );
		}

		return new $class();
	}

	/**
	 * Parse a URL, including URLs with multibyte (UTF-8) characters.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url       The URL to parse.
	 * @param int    $component Optional. A PHP_URL_* constant to return one part. Default -1 (all parts).
	 *
	 * @return mixed Same as wp_parse_url(): an array of parts, a single part, null, or false on failure.
	 */
	public static function parseUrl( $url, $component = -1 ) {
		if ( extension_loaded( 'mbstring' ) && mb_convert_encoding( $url, 'ISO-8859-1', 'UTF-8' ) === $url ) {
			return wp_parse_url( $url, $component );
		}

		// Build the reserved uri encoded characters map.
		$reservedUriCharactersMap = array(
			'%21' => '!',
			'%2A' => '*',
			'%27' => "'",
			'%28' => '(',
			'%29' => ')',
			'%3B' => ';',
			'%3A' => ':',
			'%40' => '@',
			'%26' => '&',
			'%3D' => '=',
			'%24' => '$',
			'%2C' => ',',
			'%2F' => '/',
			'%3F' => '?',
			'%23' => '#',
			'%5B' => '[',
			'%5D' => ']',
		);

		$parts = wp_parse_url( strtr( rawurlencode( $url ), $reservedUriCharactersMap ), $component );

		return $parts ? array_map( 'urldecode', $parts ) : $parts;
	}
}
