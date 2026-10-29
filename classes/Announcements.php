<?php
/**
 * Manage Announcements
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\UrlHelper;

/**
 * Announcements class
 *
 * @since 2.0.0
 */
class Announcements {
	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */

	use Backend_Page_Trait;

	/**
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		/**
		 * Handle bulk action
		 *
		 * @since 2.0.0
		 */
		add_action( 'wp_ajax_tutor_announcement_bulk_action', array( $this, 'announcement_bulk_action' ) );

		add_filter( 'tutor_learning_area_sub_page_nav_item', array( $this, 'add_subpage_nav_item' ), 10, 2 );
	}

	/**
	 * Page title fallback
	 *
	 * @since 3.5.0
	 *
	 * @param string $name Property name.
	 *
	 * @return string
	 */
	public function __get( $name ) {
		if ( 'page_title' === $name ) {
			return esc_html__( 'Announcements', 'tutor' );
		}
	}


	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function prepare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_delete(),
		);
		return $actions;
	}

	/**
	 * Handle bulk action for enrollment cancel | delete
	 *
	 * @since 2.0.0
	 *
	 * @return string JSON response.
	 */
	public function announcement_bulk_action() {
		tutor_utils()->checking_nonce();

		// Check if user is privileged.
		if ( ! User::has_any_role( array( User::ADMIN, User::INSTRUCTOR ) ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$action   = Input::post( 'bulk-action', '' );
		$bulk_ids = Input::post( 'bulk-ids', '' );

		if ( 'delete' !== $action || empty( $bulk_ids ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$raw_ids = is_array( $bulk_ids ) ? $bulk_ids : explode( ',', $bulk_ids );

		// Prevent instructor to delete admin announcement and verify permissions & post type.
		$is_admin        = User::is_admin();
		$current_user_id = get_current_user_id();

		$valid_ids = array();
		foreach ( $raw_ids as $announcement_id ) {
			$id = (int) $announcement_id;
			if ( $id <= 0 ) {
				continue;
			}

			// Verify post type is announcement.
			if ( get_post_type( $id ) !== tutor()->announcement_post_type ) {
				continue;
			}

			// Verify user can manage this announcement.
			if ( ! tutor_utils()->can_user_manage( 'announcement', $id ) ) {
				continue;
			}

			// Non-admin instructor cannot delete announcements created by administrators.
			if ( ! $is_admin ) {
				$post = get_post( $id );
				if ( ! $post ) {
					continue;
				}
				if ( User::is_admin( $post->post_author ) && (int) $post->post_author !== $current_user_id ) {
					continue;
				}
			}

			$valid_ids[] = $id;
		}

		if ( empty( $valid_ids ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
		}

		$update = self::delete_announcements( $action, $valid_ids );
		return true === $update ? wp_send_json_success() : wp_send_json_error();
	}

	/**
	 * Execute bulk action for announcements deletion
	 *
	 * @since 2.0.0
	 *
	 * @param string       $action hold action.
	 * @param string|array $bulk_ids comma separated ids or array of ids.
	 *
	 * @return bool
	 */
	public static function delete_announcements( $action, $bulk_ids ): bool {
		if ( 'delete' !== $action ) {
			return false;
		}

		if ( is_string( $bulk_ids ) ) {
			$ids = array_filter( array_map( 'intval', explode( ',', $bulk_ids ) ) );
		} elseif ( is_array( $bulk_ids ) ) {
			$ids = array_filter( array_map( 'intval', $bulk_ids ) );
		} else {
			return false;
		}

		if ( empty( $ids ) ) {
			return false;
		}

		$deleted_count = 0;
		foreach ( $ids as $id ) {
			if ( get_post_type( $id ) === tutor()->announcement_post_type ) {
				$deleted = wp_delete_post( $id, true );
				if ( $deleted ) {
					$deleted_count++;
				}
			}
		}

		return $deleted_count > 0;
	}

	/**
	 * Get announcements based on passed arguments.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args Array of query arguments for retrieving announcements.
	 *
	 * @return \WP_Query
	 */
	public static function get_announcements( array $args = array() ): \WP_Query {
		$default_args = array(
			'post_type'      => tutor()->announcement_post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'orderBy'        => 'ID',
			'order'          => 'DESC',
		);

		$args = wp_parse_args( $args, $default_args );

		return new \WP_Query( $args );
	}

	/**
	 * Add Nav Item to tutor subpage.
	 *
	 * @since 4.0.0
	 *
	 * @param array  $nav_items the array of nav items.
	 * @param string $base_url the base url.
	 *
	 * @return array
	 */
	public function add_subpage_nav_item( $nav_items, $base_url ): array {

		$nav_items['announcements'] = array(
			'title'    => __( 'Announcements', 'tutor' ),
			'icon'     => Icon::ANNOUNCEMENT,
			'url'      => UrlHelper::add_query_params( $base_url, array( 'subpage' => 'announcements' ) ),
			'template' => tutor()->path . 'templates/learning-area/subpages/announcements.php',
		);

		return $nav_items;
	}
}