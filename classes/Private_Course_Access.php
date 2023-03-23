<?php
/**
 * Private Course Access
 *
 * @package  Tutor\Course
 * @author   Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

use Tutor\Cache\TutorCache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Tutor's post types
 *
 * @since 1.0.0
 */
class Private_Course_Access {

	/**
	 * Allow empty
	 *
	 * @var boolean
	 */
	private $allow_empty = false;

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'enable_private_access' ) );
	}

	/**
	 * Enable private course access
	 *
	 * @param mixed $query query.
	 *
	 * @return void
	 */
	public function enable_private_access( $query = null ) {

		if ( ! is_admin() && is_user_logged_in() ) {

			global $wpdb;
			$p_name = isset( $query->query['name'] ) ? $query->query['name'] : '';
			$p_name = esc_sql( $p_name );

			if ( $this->allow_empty && empty( $p_name ) ) {
				$query->set( 'post_status', array( 'private', 'publish' ) );
				return;
			}

			// Get using raw query to speed up.
			$course_post_type = tutor()->course_post_type;

			// Get data from cache.
			$cache_key = "tutor_private_query_{$course_post_type}_{$p_name}";
			$result    = TutorCache::get( $cache_key );

			if ( false === $result ) {
				$private_query = $wpdb->prepare(
					"SELECT ID, post_parent
						FROM {$wpdb->posts}
						WHERE post_type = %s
							AND post_name = %s
							AND post_status = %s
					",
					$course_post_type,
					$p_name,
					'private'
				);
				$result = $wpdb->get_results( $private_query ); //phpcs:ignore
				// Set cache data.
				TutorCache::set( $cache_key, $result );
			}

			$private_course_id = ( is_array( $result ) && isset( $result[0] ) ) ? $result[0]->ID : 0;

			if ( $private_course_id > 0 && tutor_utils()->is_enrolled( $private_course_id ) ) {
				$this->allow_empty = true;
				$query->set( 'post_status', array( 'private', 'publish' ) );
			}
		}
	}
}
