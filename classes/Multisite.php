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

defined( 'ABSPATH' ) || exit;

use Tutor\Database\TableManager;

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

		add_action( 'wp_initialize_site', array( $this, 'on_initialize_site' ), 10, 2 );
		add_action( 'wp_uninitialize_site', array( $this, 'on_uninitialize_site' ), 5 );
		add_action( 'init', array( $this, 'ensure_admin_capabilities' ) );
	}

	/**
	 * Initialize Tutor on a newly created subsite.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Site $site New site object.
	 * @param array    $args Setup arguments.
	 *
	 * @return void
	 */
	public function on_initialize_site( \WP_Site $site, array $args = array() ): void {
		$blog_id = (int) $site->blog_id;

		self::run_in_site(
			$blog_id,
			function () use ( $blog_id, $site ) {
				self::provision_site();

				/**
				 * Action fired after a subsite is initialized with Tutor LMS.
				 *
				 * @since 4.3.0
				 *
				 * @param int      $blog_id Blog ID.
				 * @param \WP_Site $site    Site instance.
				 */
				do_action( 'tutor_multisite_subsite_initialized', $blog_id, $site );
			}
		);
	}

	/**
	 * Clean up tables on subsite deletion.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Site $site Site being uninitialized.
	 *
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
		if ( apply_filters( 'tutor_multisite_drop_tables_on_delete', true, $blog_id ) ) {
			self::run_in_site( $blog_id, array( TableManager::class, 'drop_tables' ) );
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
			self::run_in_site( (int) $blog_id, array( __CLASS__, 'provision_site' ) );
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

	/**
	 * Create tables and seed defaults for the current site.
	 *
	 * Tables are always created or updated so missing ones are restored.
	 * Default options, roles and pages are seeded only on a fresh install,
	 * so existing settings are never overwritten.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	private static function provision_site(): void {
		$is_installed = (bool) get_option( 'tutor_version' );

		TableManager::create_core_tables( ! $is_installed );

		if ( $is_installed ) {
			return;
		}

		update_option( 'tutor_option', Tutor::default_options() );
		Tutor::manage_tutor_roles_and_permissions();
		Tutor::save_data();
		update_option( 'tutor_version', TUTOR_VERSION );
	}

	/**
	 * Run a callback in the context of a given site, always restoring the previous one.
	 *
	 * @since 4.3.0
	 *
	 * @param int      $blog_id  Blog ID.
	 * @param callable $callback Callback to run.
	 *
	 * @return void
	 */
	private static function run_in_site( int $blog_id, callable $callback ): void {
		switch_to_blog( $blog_id );
		try {
			$callback();
		} catch ( \Exception $e ) {
			error_log(
				sprintf( 'Tutor: site initialization failed for blog ID %d: %s', $blog_id, $e->getMessage() )
			);
		} finally {
			restore_current_blog();
		}
	}
}
