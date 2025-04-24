<?php

namespace Tutor;

class ActivityLogger implements Singleton {

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
		$this->log_file = tutor()->path . '/request-log.log';
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
		$data = array(
			'timestamp' => current_time( 'mysql' ),
			'method'    => wp_unslash( $_SERVER['REQUEST_METHOD'] ) ?? 'UNKNOWN',
			'uri'       => wp_unslash( $_SERVER['REQUEST_URI'] ) ?? '',
			'user_id'   => get_current_user_id(),
			'title'     => $title,
			'trace'     => $this->get_sanitized_backtrace(),
		);

		$log_line = json_encode( $data, JSON_PRETTY_PRINT ) . PHP_EOL;
		file_put_contents( $this->log_file, $log_line, FILE_APPEND );
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
		return $sanitized;
	}
}
