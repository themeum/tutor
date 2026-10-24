<?php
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
	 * Check is test environment.
	 *
	 * @return bool
	 */
	public static function is_test_environment(): bool {
		return ( defined( 'WP_RUNNING_TESTS' ) && WP_RUNNING_TESTS )
			|| ( defined( 'WP_INSTALLING' ) && WP_INSTALLING )
			|| ( isset( $_SERVER['HTTP_X_WP_TEST_ENV'] ) && 'playwright' === $_SERVER['HTTP_X_WP_TEST_ENV'] )
			|| ( isset( $_COOKIE['wp_test_env'] ) && 'test' === $_COOKIE['wp_test_env'] )
			|| ( '1' === getenv( 'WP_TEST_ENV' ) );
	}

	/**
	 * DB setting: the live value normally, the test value in test mode.
	 *
	 * @param string $key  NAME, USER, PASS or HOST.
	 * @param string $live Value to use outside tests.
	 */
	public static function db( string $key, string $live ): string {
		if ( ! self::is_test_environment() ) {
			return $live;
		}

		$defaults = array(
			'NAME' => 'local_test',
			'USER' => 'root',
			'PASS' => 'root',
			'HOST' => $live, // Same server as live by default.
		);

		return (string) self::get( 'WP_TEST_DB_' . $key, $defaults[ $key ] ?? $live );
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
