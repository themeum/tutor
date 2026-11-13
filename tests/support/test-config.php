<?php
// @phpcs:ignoreFile

/**
 * Test environment config, loaded from wp-config.php.
 *
 * @package Tutor\Test
 */

/**
 * Class TutorTestConfig
 */
final class TutorTestConfig {
	/**
	 * The environment variables.
	 *
	 * @var array<string, string>
	 */
	private static $env = null;

	/**
	 * Live DB values passed by wp-config.php, keyed by NAME, USER, PASS, HOST.
	 *
	 * @var array<string, string>
	 */
	private static $live = array();

	/**
	 * Check is test environment.
	 *
	 * @return bool
	 */
	public static function is_test_environment(): bool {
		if ( ( defined( 'WP_RUNNING_TESTS' ) && WP_RUNNING_TESTS )
			|| ( defined( 'WP_INSTALLING' ) && WP_INSTALLING )
			|| ( '1' === getenv( 'WP_TEST_ENV' ) ) ) {
			return true;
		}

		$requested = ( isset( $_SERVER['HTTP_X_WP_TEST_ENV'] ) && 'playwright' === $_SERVER['HTTP_X_WP_TEST_ENV'] )
			|| ( isset( $_COOKIE['wp_test_env'] ) && 'test' === $_COOKIE['wp_test_env'] );

		// A header or cookie only counts on an approved host.
		return $requested && self::is_allowed_host();
	}

	/**
	 * DB setting: the live value normally, the test value in test mode.
	 *
	 * @param string $key  NAME, USER, PASS or HOST.
	 * @param string $live Value to use outside tests.
	 *
	 * @throws RuntimeException When the test database name is missing or unsafe.
	 */
	public static function db( string $key, string $live ): string {
		self::$live[ $key ] = $live;

		if ( ! self::is_test_environment() ) {
			return $live;
		}

		if ( 'NAME' === $key ) {
			$is_phpunit = ( defined( 'WP_RUNNING_TESTS' ) && WP_RUNNING_TESTS ) || '1' === getenv( 'WP_RUNNING_TESTS' );
			$name       = (string) self::get( $is_phpunit ? 'WP_PHPUNIT_DB_NAME' : 'WP_TEST_DB_NAME', '' );

			if ( '' === $name ) {
				throw new RuntimeException(
					( $is_phpunit ? 'WP_PHPUNIT_DB_NAME' : 'WP_TEST_DB_NAME' )
					. ' is missing. Run: php tests/support/install.php --yes'
				);
			}

			if ( ! preg_match( '/(_test|phpunit)/i', $name ) ) {
				throw new RuntimeException( "Refusing unsafe test database name '{$name}'." );
			}

			if ( $name === (string) ( self::$live['NAME'] ?? '' ) ) {
				throw new RuntimeException( "Refusing to use the live database '{$name}' for tests." );
			}

			return $name;
		}

		// Credentials default to the live values; only the database name is explicit.
		return (string) self::get( 'WP_TEST_DB_' . $key, $live );
	}

	/**
	 * The live database name read from wp-config.php.
	 *
	 * @return string
	 */
	public static function live_db_name(): string {
		return self::$live['NAME'] ?? '';
	}

	/**
	 * Define the constants wp-phpunit's installer reads.
	 * Fixes the "undefined constant WP_TESTS_DOMAIN" warning.
	 *
	 * @return void
	 */
	public static function define_test_constants(): void {
		if ( ! self::is_test_environment() ) {
			return;
		}

		$constants = array(
			'WP_TESTS_DOMAIN' => 'tutor-dev.local',
			'WP_TESTS_EMAIL'  => 'admin@example.org',
			'WP_TESTS_TITLE'  => 'Tutor LMS Test Site',
			'WP_PHP_BINARY'   => 'php',
		);

		foreach ( $constants as $name => $value ) {
			defined( $name ) || define( $name, $value );
		}

		// The test DB can drift from SITE_URL; pin the canonical URL so login
		// forms and redirects never point at a host the test browser cannot resolve.
		$site_url = rtrim( (string) self::get( 'SITE_URL', self::get( 'E2E_BASE_URL', 'http://localhost:8888' ) ), '/' );
		if ( preg_match( '#^https?://#i', $site_url ) ) {
			defined( 'WP_HOME' ) || define( 'WP_HOME', $site_url );
			defined( 'WP_SITEURL' ) || define( 'WP_SITEURL', $site_url );
		}

		// Keep test uploads out of the live media library.
		defined( 'UPLOADS' ) || define( 'UPLOADS', 'wp-content/uploads-e2e' );

		// Define WP_DEBUG so wp-config.php's unguarded debug block does not
		// re-define WP_DEBUG_DISPLAY and print a warning before headers.
		defined( 'WP_DEBUG' ) || define( 'WP_DEBUG', false );
		defined( 'WP_DEBUG_LOG' ) || define( 'WP_DEBUG_LOG', true );

		// Notices belong in debug.log, not in the HTML the suite asserts on.
		defined( 'WP_DEBUG_DISPLAY' ) || define( 'WP_DEBUG_DISPLAY', false );
		ini_set( 'display_errors', '0' );
	}

	/**
	 * Whether the current request host may opt into the test database.
	 *
	 * @return bool
	 */
	public static function is_allowed_host(): bool {
		$host = (string) ( $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '' );
		$host = strtolower( trim( (string) preg_replace( '/:\d+$/', '', $host ) ) );

		if ( '' === $host ) {
			return false;
		}

		$patterns = array_filter(
			array_map( 'trim', explode( ',', (string) self::get( 'WP_TEST_HOSTS', 'localhost,127.0.0.1,::1,*.local' ) ) )
		);

		foreach ( $patterns as $pattern ) {
			$pattern = strtolower( $pattern );

			if ( $pattern === $host ) {
				return true;
			}

			if ( 0 === strpos( $pattern, '*.' ) && str_ends_with( $host, substr( $pattern, 1 ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the environment variable
	 *
	 * @param string $name The name of the environment variable.
	 * @param mixed  $default The default value.
	 * @return mixed The environment variable value.
	 */
	private static function get( string $name, $default ) {
		$value = getenv( $name );
		if ( false !== $value && '' !== $value ) {
			return $value;
		}

		return self::load_env()[ $name ] ?? $default;
	}

	/**
	 * Load the environment variables from .env file.
	 *
	 * @return array The environment variables.
	 */
	private static function load_env(): array {
		if ( null === self::$env ) {
			$file      = dirname( __DIR__, 2 ) . '/.env';
			self::$env = is_readable( $file ) ? (array) parse_ini_file( $file, false, INI_SCANNER_RAW ) : array();
		}

		return self::$env;
	}
}
