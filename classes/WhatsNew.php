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

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'tutor_admin_menu', array( $this, 'add_whats_new_menu_item' ) );
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
