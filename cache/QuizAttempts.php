<?php
/**
 * Handle quiz attempt cache data
 *
 * @package Tutor\Cache
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.6
 */

namespace Tutor\Cache;

use Tutor\Cache\AbstractCache;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * User data caching
 * Get Set & check
 *
 * @since 2.0.6
 */
class QuizAttempts extends AbstractCache {

	/**
	 * Key for cache identifier
	 *
	 * @var string
	 *
	 * @since 2.0.6
	 */
	const KEY = 'tutor_quiz_attempts_count';

	/**
	 * Cache expire time
	 *
	 * @var string
	 *
	 * @since 2.0.6
	 */
	const HOUR_IN_SECONDS = 1800;

	/**
	 * Data for caching
	 *
	 * @var string
	 *
	 * @since 2.0.6
	 */
	public $data;

	/**
	 * Params for caching
	 *
	 * @var string
	 *
	 * @since 3.7.0
	 */
	public $params = array();

	/**
	 * Constructor to initialize query parameters for cache comparison.
	 *
	 * @param array $params The current query parameters (e.g., course_id, date, search).
	 *
	 * @since 2.7.0
	 */
	public function __construct( array $params = array() ) {
		$this->params = $params;
	}

	/**
	 * Key
	 *
	 * @since 2.0.6
	 * @return string
	 */
	public function key(): string {
		return self::KEY;
	}

	/**
	 * Cache data
	 *
	 * @since 2.0.6
	 * @return array
	 */
	public function cache_data() {
		return array(
			'params' => $this->params,
			'result' => $this->data,
		);
	}

	/**
	 * Cache time
	 *
	 * @since 2.0.6
	 * @return int
	 */
	public function cache_time(): int {
		$cache_time = self::HOUR_IN_SECONDS;
		return $cache_time;
	}

	/**
	 * Check if current params match cached params.
	 *
	 * @return bool
	 */
	public function is_same_query(): bool {
		$cache = $this->get_cache();
		if ( ! $cache || ! is_array( $cache ) || ! isset( $cache['params'] ) ) {
			return false;
		}
		return $cache['params'] === $this->params;
	}
}
