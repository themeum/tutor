<?php
/**
 * Whats new page handler
 *
 * @package Tutor\User
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.3.0
 */

namespace TUTOR;

/**
 * Class WhatsNew
 */
class WhatsNew {

	const WHATS_NEW_REDIRECT_TRANSIENT = 'tutor_whats_new_redirect';
	const WHATS_NEW_V4_SHOWN_OPTION    = 'tutor_whats_new_v4_shown';
	const LAST_SEEN_VERSION_OPTION     = 'tutor_last_seen_version';

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor_admin_menu', array( $this, 'add_whats_new_menu_item' ) );
		add_action( 'admin_init', array( $this, 'maybe_flag_version_bump_redirect' ), 5 );
		add_action( 'admin_init', array( $this, 'maybe_redirect_to_whats_new_v4' ) );
		add_action( 'admin_menu', array( $this, 'register_whats_new_in_v4_hidden_page' ), 99 );
	}

	/**
	 * What's new menu item
	 *
	 * @since 3.8.0
	 *
	 * @param array $menu menu.
	 *
	 * @return array
	 */
	public function add_whats_new_menu_item( $menu ) {
		$update_required = $this->get_plugin_version_info()[2];

		$menu_text = __( "What's New", 'tutor' );
		if ( $update_required ) {
			$menu_text .= ' <span class="update-plugins"><span class="plugin-count">1</span></span>';
		}

		$menu['group_three']['whats_new'] = array(
			'parent_slug' => 'tutor',
			'page_title'  => __( "What's New", 'tutor' ),
			'menu_title'  => $menu_text,
			'capability'  => 'manage_options',
			'menu_slug'   => 'tutor-whats-new',
			'callback'    => array( $this, 'whats_new_page' ),
		);

		return $menu;
	}

	/**
	 * What's new page.
	 *
	 * @since 2.2.4
	 *
	 * @return void
	 */
	public function whats_new_page() {
		list( $remote_version, $installed_version, $update_required ) = $this->get_plugin_version_info();
		$changelogs = self::build_changelog_array();

		include tutor()->path . 'views/pages/whats-new.php';
	}

	/**
	 * What's new in v4 page.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function whats_new_in_v4_page() {
		wp_enqueue_style( 'tutor-core-styles', tutor()->url . 'assets/css/tutor-core.min.css', array(), TUTOR_VERSION );
		include tutor()->path . 'views/pages/whats-new-in-v4.php';
	}

	/**
	 * Register the What's New in v4 page as a hidden submenu (no sidebar link).
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function register_whats_new_in_v4_hidden_page() {
		add_submenu_page(
			'tutor',                              // parent slug.
			__( "What's New in v4", 'tutor' ),    // page title.
			'',                                   // empty menu title = hidden from sidebar.
			'manage_options',
			'tutor-whats-new-in-v4',
			array( $this, 'whats_new_in_v4_page' )
		);
	}

	/**
	 * Detects whether this site has crossed into 4.x since we last checked,
	 * and flags a one-time redirect to the "What's New in v4" page if so.
	 *
	 * Works regardless of upgrade mechanism (admin UI, WP-CLI, manual upload,
	 * etc.) since it just compares the last recorded version against the
	 * live TUTOR_VERSION on a normal admin page load.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function maybe_flag_version_bump_redirect() {
		if ( ! is_admin() || wp_doing_ajax() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_version   = TUTOR_VERSION;
		$last_seen_version = get_option( self::LAST_SEEN_VERSION_OPTION );
		$last_seen_version = false === $last_seen_version ? false : TUTOR_VERSION;

		if ( false === $last_seen_version ) {
			update_option( self::LAST_SEEN_VERSION_OPTION, TUTOR_VERSION );

			if ( get_option( self::WHATS_NEW_V4_SHOWN_OPTION ) ) {
				return;
			}

			if ( false === get_option( 'tutor_option' ) ) {
				// Brand-new site, nothing to compare against — skip the redirect.
				update_option( self::WHATS_NEW_V4_SHOWN_OPTION, true );
				return;
			}

			// Existing site, first time we've recorded a version since this code shipped.
			if ( version_compare( $current_version, '4.0.0', '>=' ) ) {
				set_transient( self::WHATS_NEW_REDIRECT_TRANSIENT, TUTOR_VERSION, 60 );
				update_option( self::WHATS_NEW_V4_SHOWN_OPTION, true );
			}

			return;
		}

		if ( version_compare( $last_seen_version, $current_version, '>=' ) ) {
			return;
		}

		update_option( self::LAST_SEEN_VERSION_OPTION, TUTOR_VERSION );

		$crossed_into_v4 = version_compare( $last_seen_version, '4.0.0', '<' )
			&& version_compare( $current_version, '4.0.0', '>=' );

		if ( $crossed_into_v4 && ! get_option( self::WHATS_NEW_V4_SHOWN_OPTION ) ) {
			set_transient( self::WHATS_NEW_REDIRECT_TRANSIENT, TUTOR_VERSION, 60 );
			update_option( self::WHATS_NEW_V4_SHOWN_OPTION, true );
		}
	}

	/**
	 * Redirect to the What's New v4 page once after a plugin update.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function maybe_redirect_to_whats_new_v4() {
		if (
			! is_admin() ||
			wp_doing_ajax() ||
			! current_user_can( 'manage_options' ) ||
			! get_transient( self::WHATS_NEW_REDIRECT_TRANSIENT )
		) {
			return;
		}

		delete_transient( self::WHATS_NEW_REDIRECT_TRANSIENT );

		wp_safe_redirect( admin_url( 'admin.php?page=tutor-whats-new-in-v4' ) );
		exit;
	}

	/**
	 * Get tutor plugin version info
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	private function get_plugin_version_info() {
		$transient_key = 'tutor_plugin_info';
		$plugin_info   = get_transient( $transient_key );

		if ( false === $plugin_info ) {
			$plugin_info = tutils()->get_remote_plugin_info();
			set_transient( $transient_key, $plugin_info, HOUR_IN_SECONDS );
		}

		$remote_version    = $plugin_info->version ?? TUTOR_VERSION;
		$installed_version = TUTOR_VERSION;
		$update_required   = version_compare( $remote_version, $installed_version, '>' );

		return array( $remote_version, $installed_version, $update_required );
	}

	/**
	 * Whats new item.
	 *
	 * @param string $type type of item.
	 * @param array  $log changelog item.
	 *
	 * @return void
	 */
	public static function render_item( $type, $log ) {
		$obj = (object) $log;
		?>
		<li class="tutor-fs-7"><strong><?php echo esc_html( $type ); ?>:</strong> <span><?php echo esc_html( $obj->title ); ?></span> 
		<?php
		if ( isset( $obj->is_pro ) && $obj->is_pro ) :
			?>
			<span class="tutor-pro-badge">Pro</span> <?php endif; ?></li>
			<?php
	}

	/**
	 * Build changelog array for whats new page.
	 *
	 * @return array
	 */
	private static function build_changelog_array() {
		$pro_regex    = '/\((Pro|pro)\)\s*|\-\s*Pro|\-\s*pro$/';
		$new_regex    = '/^New:\s?/';
		$update_regex = '/^Update:\s?/';
		$fix_regex    = '/^Fix:\s?/';

		$lines      = self::get_latest_changelog();
		$changelogs = array();

		foreach ( $lines as $line ) {
			$line = trim( $line );
			$log  = array();

			if ( preg_match( $new_regex, $line ) ) {
				$line = preg_replace( $new_regex, '', $line );
				if ( preg_match( $pro_regex, $line ) ) {
					$line          = preg_replace( $pro_regex, '', $line );
					$log['is_pro'] = true;
				}

				$log['title']        = $line;
				$changelogs['new'][] = $log;
			}

			if ( preg_match( $update_regex, $line ) ) {
				$line = preg_replace( $update_regex, '', $line );
				if ( preg_match( $pro_regex, $line ) ) {
					$line          = preg_replace( $pro_regex, '', $line );
					$log['is_pro'] = true;
				}

				$log['title']           = $line;
				$changelogs['update'][] = $log;
			}

			if ( preg_match( $fix_regex, $line ) ) {
				$line = preg_replace( $fix_regex, '', $line );
				if ( preg_match( $pro_regex, $line ) ) {
					$line          = preg_replace( $pro_regex, '', $line );
					$log['is_pro'] = true;
				}

				$log['title']        = $line;
				$changelogs['fix'][] = $log;
			}
		}

		return $changelogs;
	}
	/**
	 * Get latest changelog from readme.txt file.
	 *
	 * @return array
	 */
	public static function get_latest_changelog() {
		$lines             = array();
		$readme_content    = file_get_contents( __DIR__ . '/../readme.txt' );
		$changelog_pattern = '/== Changelog ==\s*(.*?)==/s';

		// Perform the regex match to extract the changelog section.
		if ( preg_match( $changelog_pattern, $readme_content, $matches ) ) {
			$changelog_section = trim( $matches[1] );

			// Split the changelog section into individual entries.
			$changelog_entries = explode( '= ', $changelog_section );

			// Get the latest changelog entry.
			if ( ! empty( $changelog_entries[1] ) ) {
				$latest_changelog = $changelog_entries[1];
				$latest_changelog = preg_replace( '/(^\d+\.\d+\.\d+).*\s+/', ' ', $latest_changelog );
				$latest_changelog = trim( $latest_changelog );

				$lines = explode( "\n", $latest_changelog );
				$lines = array_map( 'trim', $lines );
			}
		}

		return $lines;
	}
}