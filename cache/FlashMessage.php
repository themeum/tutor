<?php
/**
 * Handle flash message
 *
 * @package Tutor\FlashMessage
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.1.9
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
 * @since 2.1.9
 */
class FlashMessage extends AbstractCache {

	const SUCCESS = 'success';
	const WARNING = 'warning';
	const DANGER  = 'danger';
	const INFO    = 'info';

	/**
	 * Set data property
	 *
	 * @since 2.1.9
	 *
	 * @param string $msg alert message.
	 * @param string $alert_type alert type.
	 */
	public function __construct( $msg = '', $alert_type = self::SUCCESS ) {
		if ( '' !== $msg ) {
			$this->data = array(
				'alert'   => $alert_type,
				'message' => $msg,
			);
		}
	}

	/**
	 * Key for cache identifier
	 *
	 * @var string
	 *
	 * @since 2.1.9
	 */
	private const KEY = 'tutor_flash_message';

	/**
	 * Cache expire time
	 *
	 * @var string
	 *
	 * @since 2.1.9
	 */
	private const HOUR_IN_SECONDS = 0;

	/**
	 * Data for caching
	 *
	 * @var string
	 *
	 * @since 2.1.9
	 */
	public $data;

	/**
	 * Key
	 *
	 * @since 2.1.9
	 *
	 * @return string
	 */
	public function key(): string {
		return self::KEY;
	}

	/**
	 * Cache data
	 *
	 * @since 2.1.9
	 *
	 * @return object
	 */
	public function cache_data() {
		return $this->data;
	}

	/**
	 * Cache time
	 *
	 * @since 2.1.9
	 *
	 * @return int
	 */
	public function cache_time(): int {
		$cache_time = self::HOUR_IN_SECONDS;
		return $cache_time;
	}

	/**
	 * Helper method to print & remove message
	 *
	 * This helper method assume that $this->data has single dimensional
	 * array. For ex: [alert => warning, message => operation failed ]
	 *
	 * @since 2.1.9
	 *
	 * @return void
	 */
	public function show() {
		$data = $this->get_cache();
		if ( is_array( $data ) && count( $data ) ) {
			tutor_closeable_alert_msg( $data['message'], $data['alert'] );

			// Delete flash message.
			$this->delete_cache();
		}
	}
}
