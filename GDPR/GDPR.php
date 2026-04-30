<?php
/**
 * Main GDPR module bootstrap.
 *
 * @package Tutor\GDPR
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\GDPR;

use AllowDynamicProperties;
use Tutor\GDPR\Controllers\LegalConsent;
use Tutor\GDPR\Controllers\UserConsent;
use Tutor\GDPR\DB\DB;
use TUTOR\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * GDPR main class to init GDPR functionalities.
 *
 * @since 4.0.0
 */
#[AllowDynamicProperties]
final class GDPR extends Singleton {

	/**
	 * Option key to track GDPR DB schema installation.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private const DB_SCHEMA_VERSION_OPTION = 'tutor_gdpr_db_schema_version';

	/**
	 * Current schema version.
	 *
	 * Bump this when GDPR DB schemas change.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private const DB_SCHEMA_VERSION = '1.1.0';

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->register_hooks();
	}

	/**
	 * Register WordPress hooks for GDPR.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function register_hooks() {
		add_action( 'init', array( $this, 'init' ), 5 );
	}

	/**
	 * Initialize GDPR functionalities.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		$this->maybe_install_db();

		$this->legal_consent = new LegalConsent();
		$this->user_content  = new UserConsent();
	}

	/**
	 * Create/update GDPR DB tables when needed.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function maybe_install_db() {
		$installed_version = get_option( self::DB_SCHEMA_VERSION_OPTION );
		if ( self::DB_SCHEMA_VERSION === $installed_version ) {
			return;
		}

		// Only create tables when WordPress is fully installed.
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		DB::create_tables();
		update_option( self::DB_SCHEMA_VERSION_OPTION, self::DB_SCHEMA_VERSION, false );
	}
}
