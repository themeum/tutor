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
	 * @return object
	 */
	public function cache_data() {
		return $this->data;
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
}
