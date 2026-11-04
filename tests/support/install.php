<?php
/**
 * Test Environment Installer.
 *
 * Sets up .env configuration, creates the test database,
 * ensures wp-config.php test routing, and seeds initial fixtures.
 *
 * Usage:
 *   composer test:install
 *   php tests/support/install.php
 *
 * @package Tutor\Tests
 */

// @phpcs:ignoreFile

echo "==> Configuring Tutor LMS test environment...\n";

$plugin_dir    = dirname( __DIR__, 2 );
$env_file      = $plugin_dir . '/.env';
$env_example   = $plugin_dir . '/.env.example';
$wp_config_file = dirname( __DIR__, 5 ) . '/wp-config.php';

if ( ! file_exists( $wp_config_file ) ) {
	fwrite( STDERR, "wp-config.php is missing at " . $wp_config_file );
	exit(1);
}

// 1. Extract live database configuration from wp-config.php.
$live_db_name = 'local';
$live_db_user = 'root';
$live_db_pass = 'root';
$live_db_host = '127.0.0.1:10003';


$wp_config_content = file_get_contents( $wp_config_file );

// Match TutorTestConfig::db( '...', '...' ) or direct define( 'DB_...', '...' )
if ( preg_match( "/TutorTestConfig::db\s*\(\s*['\"]NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/i", $wp_config_content, $m ) ) {
	$live_db_name = $m[1];
} elseif ( preg_match( "/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/i", $wp_config_content, $m ) ) {
	$live_db_name = $m[1];
}

if ( preg_match( "/TutorTestConfig::db\s*\(\s*['\"]USER['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/i", $wp_config_content, $m ) ) {
	$live_db_user = $m[1];
} elseif ( preg_match( "/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/i", $wp_config_content, $m ) ) {
	$live_db_user = $m[1];
}

if ( preg_match( "/TutorTestConfig::db\s*\(\s*['\"]PASS['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/i", $wp_config_content, $m ) ) {
	$live_db_pass = $m[1];
} elseif ( preg_match( "/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/i", $wp_config_content, $m ) ) {
	$live_db_pass = $m[1];
}

if ( preg_match( "/TutorTestConfig::db\s*\(\s*['\"]HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/i", $wp_config_content, $m ) ) {
	$live_db_host = $m[1];
} elseif ( preg_match( "/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/i", $wp_config_content, $m ) ) {
	$live_db_host = $m[1];
}


// Derive test DB name by appending '_test' suffix if not already present.
$test_db_name = preg_match( '/_test$/i', $live_db_name ) ? $live_db_name : $live_db_name . '_test';

// 2. Ensure .env exists.
if ( ! file_exists( $env_file ) ) {
	if ( file_exists( $env_example ) ) {
		copy( $env_example, $env_file );
		echo "==> Created .env from .env.example\n";
	} else {
		file_put_contents( $env_file, '' );
		echo "==> Created new .env file\n";
	}
}

// 3. Ensure test database variables exist in .env using values from wp-config.php.
$required_keys = array(
	'WP_TEST_DB_NAME' => $test_db_name,
	'WP_TEST_DB_USER' => $live_db_user,
	'WP_TEST_DB_PASS' => $live_db_pass,
	'WP_TEST_DB_HOST' => $live_db_host,
);

$env_content = file_exists( $env_file ) ? file_get_contents( $env_file ) : '';
$keys_added  = array();

foreach ( $required_keys as $key => $default_val ) {
	// Check if active (uncommented) definition exists.
	if ( ! preg_match( '/^\s*' . preg_quote( $key, '/' ) . '\s*=/m', $env_content ) ) {
		if ( '' !== $env_content && substr( $env_content, -1 ) !== "\n" ) {
			$env_content .= "\n";
		}
		$env_content .= "{$key}={$default_val}\n";
		$keys_added[] = $key;
	}
}

if ( ! empty( $keys_added ) ) {
	file_put_contents( $env_file, $env_content );
	echo "==> Added missing config to .env: " . implode( ', ', $keys_added ) . "\n";
}

// 3. Load DB settings from .env / environment.
$parsed_env = parse_ini_file( $env_file, false, INI_SCANNER_RAW ) ?: array();

$db_name = getenv( 'WP_TEST_DB_NAME' ) ?: ( $parsed_env['WP_TEST_DB_NAME'] ?? $required_keys['WP_TEST_DB_NAME'] );
$db_user = getenv( 'WP_TEST_DB_USER' ) ?: ( $parsed_env['WP_TEST_DB_USER'] ?? $required_keys['WP_TEST_DB_USER'] );
$db_pass = getenv( 'WP_TEST_DB_PASS' ) ?: ( $parsed_env['WP_TEST_DB_PASS'] ?? $required_keys['WP_TEST_DB_PASS'] );
$db_host = getenv( 'WP_TEST_DB_HOST' ) ?: ( $parsed_env['WP_TEST_DB_HOST'] ?? $required_keys['WP_TEST_DB_HOST'] );

// 4. Create the test database if it does not exist.
$host   = $db_host;
$port   = 3306;
$socket = null;

if ( strpos( $host, ':/' ) !== false ) {
	list( $host, $socket ) = explode( ':', $host, 2 );
} elseif ( strpos( $host, ':' ) !== false ) {
	list( $host, $port_str ) = explode( ':', $host, 2 );
	$port = (int) $port_str;
}

echo "==> Ensuring test database '{$db_name}' exists on {$db_host}...\n";

$db_created = false;
$error_msg  = '';

if ( class_exists( 'mysqli' ) ) {
	$mysqli = @new mysqli( $host, $db_user, $db_pass, '', $port, $socket );
	if ( $mysqli->connect_errno ) {
		$error_msg = "mysqli connection failed ({$mysqli->connect_errno}): {$mysqli->connect_error}";
	} else {
		$escaped_db = str_replace( '`', '``', $db_name );
		$query      = "CREATE DATABASE IF NOT EXISTS `{$escaped_db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci";
		if ( $mysqli->query( $query ) ) {
			$db_created = true;
		} else {
			$error_msg = "Query error: {$mysqli->error}";
		}
		$mysqli->close();
	}
}

if ( ! $db_created && class_exists( 'PDO' ) ) {
	try {
		$dsn = "mysql:host={$host}";
		if ( $port ) {
			$dsn .= ";port={$port}";
		}
		if ( $socket ) {
			$dsn .= ";unix_socket={$socket}";
		}
		$pdo = new PDO( $dsn, $db_user, $db_pass, array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ) );
		$escaped_db = str_replace( '`', '``', $db_name );
		$pdo->exec( "CREATE DATABASE IF NOT EXISTS `{$escaped_db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci" );
		$db_created = true;
	} catch ( PDOException $e ) {
		$error_msg = $e->getMessage();
	}
}

if ( ! $db_created ) {
	fwrite( STDERR, "\n[FATAL ERROR] Could not create database '{$db_name}': {$error_msg}\n" );
	fwrite( STDERR, "Please verify your MySQL server is running and credentials in .env are correct.\n" );
	exit( 1 );
}

echo "==> Database '{$db_name}' ready.\n";

// 5. Ensure wp-config.php routes to test database and skips wp-settings during tests.
if ( file_exists( $wp_config_file ) ) {
	$wp_config_content = file_get_contents( $wp_config_file );
	$updated           = false;

	// 5.1 Route DB settings to test-config.php if not already present.
	if ( strpos( $wp_config_content, 'test-config.php' ) === false ) {
		echo "==> Adding test database routing to wp-config.php...\n";
		$test_config_block = <<<PHP
\$tutor_test_config = __DIR__ . '/wp-content/plugins/tutor/tests/support/test-config.php';

if ( file_exists( \$tutor_test_config ) ) {
	require_once \$tutor_test_config;
	define( 'DB_NAME', TutorTestConfig::db( 'NAME', '{$live_db_name}' ) );
	define( 'DB_USER', TutorTestConfig::db( 'USER', '{$live_db_user}' ) );
	define( 'DB_PASSWORD', TutorTestConfig::db( 'PASS', '{$live_db_pass}' ) );
	define( 'DB_HOST', TutorTestConfig::db( 'HOST', '{$live_db_host}' ) );
	TutorTestConfig::define_test_constants();
} else {
PHP;

		// Replace standard DB_NAME / DB_USER definitions or prepend before DB_CHARSET.
		$pattern = '/\/\*\* Database charset to use/i';
		if ( preg_match( $pattern, $wp_config_content ) ) {
			// Check if standard define('DB_NAME' ... define('DB_HOST' exists before it.
			$db_defines_pattern = '/(define\s*\(\s*[\'"]DB_NAME[\'"].*?define\s*\(\s*[\'"]DB_HOST[\'"].*?\;\n)/s';
			if ( preg_match( $db_defines_pattern, $wp_config_content, $matches ) ) {
				$lines          = explode( "\n", trim( $matches[1] ) );
				$indented_lines = array_map(
					function( $line ) {
						$trimmed = trim( $line );
						return '' !== $trimmed ? "\t" . $trimmed : '';
					},
					$lines
				);
				$else_body         = implode( "\n", $indented_lines );
				$replacement       = "{$test_config_block}\n{$else_body}\n}\n";
				$wp_config_content = str_replace( $matches[1], $replacement, $wp_config_content );
				$updated           = true;
			}
		}
	} else {
		echo "==> wp-config.php test database routing confirmed.\n";
	}

	// 5.2 Ensure wp-settings.php is wrapped with WP_RUNNING_TESTS check.
	if ( ! preg_match( '/defined\s*\(\s*[\'"]WP_RUNNING_TESTS[\'"]\s*\)/i', $wp_config_content ) ) {
		$settings_pattern = '/require(?:_once)?\s*\(?\s*ABSPATH\s*\.\s*[\'"]\/?wp-settings\.php[\'"]\s*\)?\s*;/i';
		if ( preg_match( $settings_pattern, $wp_config_content, $matches ) ) {
			echo "==> Adding test environment check around wp-settings.php in wp-config.php...\n";
			$wrapped_settings  = "if ( ! defined( 'WP_RUNNING_TESTS' ) && ! defined( 'WP_INSTALLING' ) ) {\n\trequire_once ABSPATH . 'wp-settings.php';\n}";
			$wp_config_content = str_replace( $matches[0], $wrapped_settings, $wp_config_content );
			$updated           = true;
		}
	} else {
		echo "==> wp-config.php wp-settings.php test check confirmed.\n";
	}

	if ( $updated ) {
		file_put_contents( $wp_config_file . '.bak', file_get_contents( $wp_config_file ) );
		file_put_contents( $wp_config_file, $wp_config_content );
		echo "==> wp-config.php updated (backup created at wp-config.php.bak)\n";
	}
}

// 6. Reset test database and seed fixtures.
echo "==> Resetting database and seeding fixtures...\n";
chdir( $plugin_dir );
passthru( 'composer test:db:reset', $return_code );

if ( 0 !== $return_code ) {
	fwrite( STDERR, "\n[ERROR] Database reset failed with exit code {$return_code}\n" );
	exit( $return_code );
}

// 7. Show success banner.
echo "\n";
echo "======================================================================\n";
echo " [SUCCESS] Test environment installed successfully!\n";
echo "======================================================================\n\n";

// 8. Prompt to run verification test.
$is_interactive = function_exists( 'stream_isatty' ) ? stream_isatty( STDIN ) : true;

if ( $is_interactive ) {
	echo "Want to run a unit test to verify configuration? (y/n) [y]: ";
	$handle = fopen( 'php://stdin', 'r' );
	$answer = $handle ? trim( (string) fgets( $handle ) ) : '';
	if ( $handle ) {
		fclose( $handle );
	}

	if ( '' === $answer || in_array( strtolower( $answer ), array( 'y', 'yes' ), true ) ) {
		echo "\n==> Running 'composer test:unit:filter DBTest'...\n\n";
		chdir( $plugin_dir );
		passthru( 'composer test:unit:filter DBTest' );
	} else {
		echo "\nSkipped. You can run tests at any time with:\n  composer test:unit:filter DBTest\n\n";
	}
} else {
	echo "Check that it works: composer test:unit:filter DBTest\n\n";
}
