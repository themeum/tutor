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

/**
 * Tiny console helper: colors, symbols and step output.
 */
final class Console {

	private static bool $color = false;

	public static function init(): void {
		$no_color = false !== getenv( 'NO_COLOR' );
		$is_tty   = function_exists( 'stream_isatty' ) && @stream_isatty( STDOUT );

		// Enable ANSI sequences on modern Windows terminals.
		if ( $is_tty && DIRECTORY_SEPARATOR === '\\' && function_exists( 'sapi_windows_vt100_support' ) ) {
			$is_tty = @sapi_windows_vt100_support( STDOUT, true );
		}

		self::$color = $is_tty && ! $no_color;
	}

	private static function paint( string $text, string $code ): string {
		return self::$color ? "\033[{$code}m{$text}\033[0m" : $text;
	}

	public static function green( string $text ): string {
		return self::paint( $text, '32' );
	}

	public static function red( string $text ): string {
		return self::paint( $text, '31' );
	}

	public static function dim( string $text ): string {
		return self::paint( $text, '2' );
	}

	public static function bold( string $text ): string {
		return self::paint( $text, '1' );
	}

	public static function line( string $text = '' ): void {
		echo $text . "\n";
	}

	public static function title( string $text ): void {
		self::line();
		self::line( self::bold( $text ) );
		self::line( self::dim( str_repeat( '─', 60 ) ) );
	}

	public static function ok( string $label, string $detail = '' ): void {
		$detail = '' !== $detail ? ' ' . self::dim( $detail ) : '';
		self::line( ' ' . self::green( '✔' ) . ' ' . $label . $detail );
	}

	public static function fail( string $label, string $reason = '' ): void {
		self::line( ' ' . self::red( '✘' ) . ' ' . self::red( $label ) );
		if ( '' !== $reason ) {
			foreach ( explode( "\n", trim( $reason ) ) as $row ) {
				self::line( '   ' . self::red( '│' ) . ' ' . $row );
			}
		}
	}

	public static function error( string $text ): void {
		fwrite( STDERR, self::red( $text ) . "\n" );
	}
}

/**
 * Installs the Tutor LMS test environment.
 */
final class Tutor_Test_Installer {

	private string $plugin_dir;
	private string $env_file;
	private string $env_example;
	private string $wp_config_file;

	/** @var array<string,string> Live DB settings read from wp-config.php. */
	private array $live_db = array(
		'name' => 'local',
		'user' => 'root',
		'pass' => 'root',
		'host' => '127.0.0.1:10003',
	);

	/** @var array<string,string> Resolved test DB settings. */
	private array $db = array();

	private string $test_db_name = '';
	private bool $config_backed_up = false;

	public function __construct() {
		$this->plugin_dir     = dirname( __DIR__, 2 );
		$this->env_file       = $this->plugin_dir . '/.env';
		$this->env_example    = $this->plugin_dir . '/.env.example';
		$this->wp_config_file = dirname( __DIR__, 5 ) . '/wp-config.php';
	}

	/**
	 * Run every step in order. Stops at the first failure.
	 */
	public function run(): void {
		Console::title( 'Tutor LMS · Test Environment Installer' );

		$this->step( 'Locate wp-config.php', array( $this, 'locate_wp_config' ) );
		$this->step( 'Read database settings from wp-config.php', array( $this, 'read_live_db' ) );
		$this->step( 'Prepare .env file', array( $this, 'prepare_env' ) );
		$this->step( 'Sync test database variables in .env', array( $this, 'sync_env_keys' ) );
		$this->step( 'Resolve test database settings', array( $this, 'resolve_db' ) );
		$this->step( 'Create test database', array( $this, 'create_database' ) );
		$this->step( 'Route wp-config.php to test database', array( $this, 'route_wp_config' ) );
		$this->step( 'Guard wp-settings.php during tests', array( $this, 'guard_wp_settings' ) );
		$this->step( 'Reset database and seed fixtures', array( $this, 'reset_database' ) );

		Console::line();
		Console::line( ' ' . Console::green( '✔' ) . ' ' . Console::bold( Console::green( 'Test environment installed successfully!' ) ) );
		Console::line();

		$this->verify();
	}

	/**
	 * Run a single step, printing a green check on success or a red cross on failure.
	 *
	 * @param string   $label    Step label.
	 * @param callable $callback Returns an optional detail string; throws on failure.
	 */
	private function step( string $label, callable $callback ): void {
		try {
			$detail = (string) $callback();
			Console::ok( $label, $detail );
		} catch ( Throwable $e ) {
			Console::fail( $label, $e->getMessage() );
			Console::line();
			exit( 1 );
		}
	}

	/* ---------------------------------------------------------------------
	 * Steps
	 * ------------------------------------------------------------------ */

	private function locate_wp_config(): string {
		if ( ! file_exists( $this->wp_config_file ) ) {
			throw new RuntimeException( 'wp-config.php is missing at ' . $this->wp_config_file );
		}
		return $this->wp_config_file;
	}

	private function read_live_db(): string {
		$content = $this->read_wp_config();

		$this->live_db['name'] = $this->find_setting( $content, 'NAME', 'DB_NAME', $this->live_db['name'] );
		$this->live_db['user'] = $this->find_setting( $content, 'USER', 'DB_USER', $this->live_db['user'], true );
		$this->live_db['pass'] = $this->find_setting( $content, 'PASS', 'DB_PASSWORD', $this->live_db['pass'], true );
		$this->live_db['host'] = $this->find_setting( $content, 'HOST', 'DB_HOST', $this->live_db['host'] );

		// Append '_test' unless the name already ends with it.
		$this->test_db_name = preg_match( '/_test$/i', $this->live_db['name'] )
			? $this->live_db['name']
			: $this->live_db['name'] . '_test';

		return "{$this->live_db['name']} @ {$this->live_db['host']}";
	}

	private function prepare_env(): string {
		if ( file_exists( $this->env_file ) ) {
			return '.env already exists';
		}

		if ( file_exists( $this->env_example ) ) {
			if ( ! copy( $this->env_example, $this->env_file ) ) {
				throw new RuntimeException( 'Could not copy .env.example to .env' );
			}
			return 'created from .env.example';
		}

		if ( false === file_put_contents( $this->env_file, '' ) ) {
			throw new RuntimeException( 'Could not create .env' );
		}
		return 'created empty .env';
	}

	private function sync_env_keys(): string {
		$required = array(
			'WP_TEST_DB_NAME' => $this->test_db_name,
			'WP_TEST_DB_USER' => $this->live_db['user'],
			'WP_TEST_DB_PASS' => $this->live_db['pass'],
			'WP_TEST_DB_HOST' => $this->live_db['host'],
		);

		$content = (string) file_get_contents( $this->env_file );
		$added   = array();
		$block   = '';

		foreach ( $required as $key => $value ) {
			// Only an active (uncommented) definition counts.
			if ( preg_match( '/^\s*' . preg_quote( $key, '/' ) . '\s*=/m', $content ) ) {
				continue;
			}
			$block  .= "{$key}={$value}\n";
			$added[] = $key;
		}

		if ( $added ) {
			// Separate the new block from existing content with one blank line.
			$content = '' === trim( $content ) ? '' : rtrim( $content ) . "\n\n";
			$content .= $block;

			if ( false === file_put_contents( $this->env_file, $content ) ) {
				throw new RuntimeException( 'Could not write to .env' );
			}
			return 'added ' . implode( ', ', $added );
		}

		return 'all variables already present';
	}

	private function resolve_db(): string {
		$parsed = @parse_ini_file( $this->env_file, false, INI_SCANNER_RAW ) ?: array();

		$map = array(
			'name' => array( 'WP_TEST_DB_NAME', $this->test_db_name ),
			'user' => array( 'WP_TEST_DB_USER', $this->live_db['user'] ),
			'pass' => array( 'WP_TEST_DB_PASS', $this->live_db['pass'] ),
			'host' => array( 'WP_TEST_DB_HOST', $this->live_db['host'] ),
		);

		foreach ( $map as $field => list( $env_key, $fallback ) ) {
			$this->db[ $field ] = getenv( $env_key ) ?: ( $parsed[ $env_key ] ?? $fallback );
		}

		return "{$this->db['name']} @ {$this->db['host']}";
	}

	private function create_database(): string {
		list( $host, $port, $socket ) = $this->parse_host( $this->db['host'] );

		$name   = str_replace( '`', '``', $this->db['name'] );
		$query  = "CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci";
		$errors = array();

		if ( class_exists( 'mysqli' ) ) {
			try {
				mysqli_report( MYSQLI_REPORT_OFF );
				$mysqli = @new mysqli( $host, $this->db['user'], $this->db['pass'], '', $port, $socket );

				if ( $mysqli->connect_errno ) {
					$errors[] = "mysqli connection failed ({$mysqli->connect_errno}): {$mysqli->connect_error}";
				} elseif ( $mysqli->query( $query ) ) {
					$mysqli->close();
					return $this->db['name'];
				} else {
					$errors[] = "mysqli query error: {$mysqli->error}";
					$mysqli->close();
				}
			} catch ( Throwable $e ) {
				$errors[] = 'mysqli: ' . $e->getMessage();
			}
		}

		if ( class_exists( 'PDO' ) ) {
			try {
				$dsn = "mysql:host={$host};port={$port}";
				if ( $socket ) {
					$dsn .= ";unix_socket={$socket}";
				}
				$pdo = new PDO( $dsn, $this->db['user'], $this->db['pass'], array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ) );
				$pdo->exec( $query );
				return $this->db['name'];
			} catch ( PDOException $e ) {
				$errors[] = 'PDO: ' . $e->getMessage();
			}
		}

		$errors[] = "Make sure MySQL is running and the credentials in .env are correct ({$this->db['host']}).";
		throw new RuntimeException( implode( "\n", $errors ) );
	}

	private function route_wp_config(): string {
		$content = $this->read_wp_config();

		if ( false !== strpos( $content, 'test-config.php' ) ) {
			return 'already configured';
		}

		$pattern = '/(define\s*\(\s*[\'"]DB_NAME[\'"].*?define\s*\(\s*[\'"]DB_HOST[\'"].*?;\n)/s';
		if ( ! preg_match( $pattern, $content, $matches ) ) {
			throw new RuntimeException( 'Could not find the DB_NAME … DB_HOST define() block in wp-config.php.' );
		}

		$else_body = implode(
			"\n",
			array_map(
				static function ( $line ) {
					$line = trim( $line );
					return '' !== $line ? "\t" . $line : '';
				},
				explode( "\n", trim( $matches[1] ) )
			)
		);

		$block = strtr(
			<<<'PHP'
$tutor_test_config = __DIR__ . '/wp-content/plugins/tutor/tests/support/test-config.php';

if ( file_exists( $tutor_test_config ) ) {
	require_once $tutor_test_config;
	define( 'DB_NAME', TutorTestConfig::db( 'NAME', '{{NAME}}' ) );
	define( 'DB_USER', TutorTestConfig::db( 'USER', '{{USER}}' ) );
	define( 'DB_PASSWORD', TutorTestConfig::db( 'PASS', '{{PASS}}' ) );
	define( 'DB_HOST', TutorTestConfig::db( 'HOST', '{{HOST}}' ) );
	TutorTestConfig::define_test_constants();
} else {
{{ELSE}}
}

PHP,
			array(
				'{{NAME}}' => $this->live_db['name'],
				'{{USER}}' => $this->live_db['user'],
				'{{PASS}}' => $this->live_db['pass'],
				'{{HOST}}' => $this->live_db['host'],
				'{{ELSE}}' => $else_body,
			)
		);

		$this->write_wp_config( str_replace( $matches[1], $block, $content ) );

		return 'wp-config.php updated';
	}

	private function guard_wp_settings(): string {
		$content = $this->read_wp_config();

		if ( preg_match( '/defined\s*\(\s*[\'"]WP_RUNNING_TESTS[\'"]\s*\)/i', $content ) ) {
			return 'already guarded';
		}

		$pattern = '/require(?:_once)?\s*\(?\s*ABSPATH\s*\.\s*[\'"]\/?wp-settings\.php[\'"]\s*\)?\s*;/i';
		if ( ! preg_match( $pattern, $content, $matches ) ) {
			throw new RuntimeException( 'Could not find the wp-settings.php require statement in wp-config.php.' );
		}

		$wrapped = "if ( ! defined( 'WP_RUNNING_TESTS' ) && ! defined( 'WP_INSTALLING' ) ) {\n\trequire_once ABSPATH . 'wp-settings.php';\n}";
		$this->write_wp_config( str_replace( $matches[0], $wrapped, $content ) );

		return 'wp-config.php updated';
	}

	private function reset_database(): string {
		$code = $this->composer( 'test:db:reset', $output );

		if ( 0 !== $code ) {
			throw new RuntimeException( "composer test:db:reset exited with code {$code}\n" . $output );
		}

		return 'fixtures seeded';
	}

	/**
	 * Optional verification test.
	 */
	private function verify(): void {
		$command        = 'test:unit:filter DBTest';
		$is_interactive = function_exists( 'stream_isatty' ) ? stream_isatty( STDIN ) : true;

		if ( ! $is_interactive ) {
			Console::line( Console::dim( " Check that it works: composer {$command}" ) );
			Console::line();
			return;
		}

		echo ' Run a unit test to verify the configuration? (y/n) [y]: ';
		$handle = fopen( 'php://stdin', 'r' );
		$answer = $handle ? strtolower( trim( (string) fgets( $handle ) ) ) : '';
		if ( $handle ) {
			fclose( $handle );
		}

		if ( '' !== $answer && ! in_array( $answer, array( 'y', 'yes' ), true ) ) {
			Console::line();
			Console::line( Console::dim( " Skipped. Run it any time with: composer {$command}" ) );
			Console::line();
			return;
		}

		Console::line();
		$code = $this->composer( $command, $output );

		if ( 0 === $code ) {
			Console::ok( 'Verification test passed', 'DBTest' );
		} else {
			Console::fail( 'Verification test failed', $output );
		}
		Console::line();

		exit( $code );
	}

	/* ---------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------ */

	private function read_wp_config(): string {
		$content = file_get_contents( $this->wp_config_file );
		if ( false === $content ) {
			throw new RuntimeException( 'Could not read ' . $this->wp_config_file );
		}
		return $content;
	}

	/**
	 * Write wp-config.php, creating a backup once per run.
	 */
	private function write_wp_config( string $content ): void {
		if ( ! $this->config_backed_up ) {
			if ( false === copy( $this->wp_config_file, $this->wp_config_file . '.bak' ) ) {
				throw new RuntimeException( 'Could not create wp-config.php.bak backup.' );
			}
			$this->config_backed_up = true;
		}

		if ( false === file_put_contents( $this->wp_config_file, $content ) ) {
			throw new RuntimeException( 'Could not write ' . $this->wp_config_file );
		}
	}

	/**
	 * Find a setting in wp-config.php, either as TutorTestConfig::db( KEY, 'value' )
	 * or as define( 'CONSTANT', 'value' ).
	 */
	private function find_setting( string $content, string $key, string $constant, string $default, bool $allow_empty = false ): string {
		$quantifier = $allow_empty ? '*' : '+';
		$value      = "['\"]([^'\"]{$quantifier})['\"]";

		$patterns = array(
			"/TutorTestConfig::db\s*\(\s*['\"]{$key}['\"]\s*,\s*{$value}\s*\)/i",
			"/define\s*\(\s*['\"]{$constant}['\"]\s*,\s*{$value}\s*\)/i",
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $content, $m ) ) {
				return $m[1];
			}
		}

		return $default;
	}

	/**
	 * Split "host:port" or "host:/path/to/socket".
	 *
	 * @return array{0:string,1:int,2:?string}
	 */
	private function parse_host( string $host ): array {
		$port   = 3306;
		$socket = null;

		if ( false !== strpos( $host, ':/' ) ) {
			list( $host, $socket ) = explode( ':', $host, 2 );
		} elseif ( false !== strpos( $host, ':' ) ) {
			list( $host, $port_str ) = explode( ':', $host, 2 );
			$port                    = (int) $port_str;
		}

		return array( $host, $port, $socket );
	}

	/**
	 * Run a composer script quietly, capturing output.
	 */
	private function composer( string $script, ?string &$output = null ): int {
		$previous = getcwd();
		chdir( $this->plugin_dir );

		$lines = array();
		exec( 'composer ' . $script . ' 2>&1', $lines, $code );
		$output = implode( "\n", $lines );

		if ( $previous ) {
			chdir( $previous );
		}

		return $code;
	}
}

Console::init();
( new Tutor_Test_Installer() )->run();