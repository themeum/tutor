<?php
/**
 * Activity logger
 *
 * @package Tutor\ActivityLogger
 *
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.5.0
 */

namespace TUTOR;

/**
 * Log activities
 *
 * @since 3.5.0
 */
class Logger extends Singleton {

	/**
	 * Log file
	 *
	 * @since 3.5.0
	 *
	 * @var string
	 */
	protected $log_file;

	/**
	 * Resolve initial task
	 *
	 * @since 3.5.0
	 */
	public function __construct() {
		$this->log_file = tutor()->path . '/logger.log';
	}

	/**
	 * Log the request with backtrace
	 *
	 * @since 3.5.0
	 *
	 * @param string $title Log title.
	 *
	 * @return void
	 */
	public function log( $title ) {
		global $wp_filesystem;

		// Ensure WP_Filesystem is loaded.
		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			error_log( 'Failed to initialize WP_Filesystem.' );
			return;
		}

		// Prepare log data.
		$data     = array(
			'timestamp' => current_time( 'mysql' ),
			'method'    => wp_unslash( $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN' ),
			'uri'       => wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ),
			'user_id'   => get_current_user_id(),
			'title'     => $title,
			'trace'     => $this->get_sanitized_backtrace(),
		);
		$log_line = json_encode( $data, JSON_PRETTY_PRINT ) . PHP_EOL;

		// Check if the log file exists, create if not.
		if ( ! $wp_filesystem->exists( $this->log_file ) ) {
			$created = $wp_filesystem->put_contents( $this->log_file, '', FS_CHMOD_FILE );
			if ( ! $created ) {
				error_log( 'Failed to create log file: ' . $this->log_file );
				return;
			}
		}

		// Check if the log file is writable.
		if ( ! $wp_filesystem->is_writable( $this->log_file ) ) {
			error_log( 'Log file is not writable: ' . $this->log_file );
			return;
		}

		// Read existing content and append the new line.
		$current_content = $wp_filesystem->get_contents( $this->log_file );
		$wp_filesystem->put_contents( $this->log_file, $current_content . $log_line, FS_CHMOD_FILE );
	}


	/**
	 * Get backtrace data
	 *
	 * @since 3.5.0
	 *
	 * @return array
	 */
	private function get_sanitized_backtrace() {
		$trace     = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$sanitized = array();

		foreach ( $trace as $entry ) {
			if ( isset( $entry['file'], $entry['line'], $entry['function'] ) ) {
				$sanitized[] = "{$entry['file']} ({$entry['line']}) -> {$entry['function']}";
			}
		}
		return array_reverse( $sanitized );
	}
}
