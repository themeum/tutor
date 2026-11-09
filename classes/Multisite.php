<?php
/**
 * Handle multisite support for Tutor LMS.
 *
 * @package TUTOR
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.3.0
 */

namespace TUTOR;

use Tutor\Database\TableManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Multisite
 *
 * @since 4.3.0
 */
class Multisite {

	/**
	 * Constructor.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		if ( ! is_multisite() ) {
			return;
		}

		// Modern site initialization hook (WP 5.1+).
		add_action( 'wp_initialize_site', array( $this, 'on_initialize_site' ), 10, 2 );

		// Site deletion hook.
		add_action( 'wp_uninitialize_site', array( $this, 'on_uninitialize_site' ) );

		// Ensure administrator capabilities on active subsite.
		add_action( 'init', array( $this, 'ensure_admin_capabilities' ) );
	}

	/**
	 * Initialize Tutor on a newly created subsite.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Site $site New site object.
	 * @param array    $args Setup arguments.
	 * @return void
	 */
	public function on_initialize_site( \WP_Site $site, array $args = array() ): void {
		$blog_id = (int) $site->blog_id;

		switch_to_blog( $blog_id );

		// 1. Create all tables.
		TableManager::create_tables( true );

		// 2. Default options.
		$default_options = Tutor::default_options();
		update_option( 'tutor_option', $default_options );
		update_option( 'tutor_version', TUTOR_VERSION );

		// 3. User roles & permissions.
		Tutor::manage_tutor_roles_and_permissions();

		// 4. Default pages.
		Tutor::save_data();

		/**
		 * Action fired after a subsite is initialized with Tutor LMS.
		 *
		 * @since 4.3.0
		 *
		 * @param int      $blog_id Blog ID.
		 * @param \WP_Site $site    Site instance.
		 */
		do_action( 'tutor_multisite_subsite_initialized', $blog_id, $site );

		restore_current_blog();
	}

	/**
	 * Clean up tables on subsite deletion.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Site $site Site being uninitialized.
	 * @return void
	 */
	public function on_uninitialize_site( \WP_Site $site ): void {
		$blog_id = (int) $site->blog_id;

		/**
		 * Filter whether to drop Tutor tables when a subsite is deleted.
		 *
		 * @since 4.3.0
		 *
		 * @param bool $should_drop Whether to drop tables. Default true.
		 * @param int  $blog_id     Blog ID being deleted.
		 */
		$should_drop = apply_filters( 'tutor_multisite_drop_tables_on_delete', true, $blog_id );
		if ( $should_drop ) {
			switch_to_blog( $blog_id );
			TableManager::drop_tables();
			restore_current_blog();
		}

		/**
		 * Action fired after subsite tables have been cleaned up.
		 *
		 * @since 4.3.0
		 *
		 * @param int      $blog_id Blog ID.
		 * @param \WP_Site $site    Site instance.
		 */
		do_action( 'tutor_multisite_subsite_deleted', $blog_id, $site );
	}

	/**
	 * Network activation runner to provision all existing sites in network.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public static function activate_network(): void {
		$site_ids = get_sites(
			array(
				'fields' => 'ids',
				'number' => 0,
			)
		);

		foreach ( $site_ids as $blog_id ) {
			switch_to_blog( (int) $blog_id );

			TableManager::create_tables( true );

			$version = get_option( 'tutor_version' );
			if ( ! $version ) {
				update_option( 'tutor_option', Tutor::default_options() );
				Tutor::manage_tutor_roles_and_permissions();
				Tutor::save_data();
				update_option( 'tutor_version', TUTOR_VERSION );
			}

			restore_current_blog();
		}
	}

	/**
	 * Ensure administrator users have required Tutor capabilities on the subsite.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function ensure_admin_capabilities(): void {
		if ( current_user_can( 'administrator' ) && ! current_user_can( tutor()->instructor_role ) ) {
			$user = wp_get_current_user();
			if ( $user && $user->exists() ) {
				$user->add_role( 'manage_tutor' );
				$user->add_role( tutor()->instructor_role );
			}
		}
	}
}

