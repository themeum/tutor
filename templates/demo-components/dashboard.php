<?php
/**
 * Tutor dashboard.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Input;

?>
<?php require 'dashboard/components/settings/account.php'; ?>

<div class="tutor-dashboard-layout">
	<?php tutor_load_template( 'demo-components.dashboard.components.sidebar' ); ?>
	<div class="tutor-dashboard-main">
		<?php tutor_load_template( 'demo-components.dashboard.components.header' ); ?>
		<div class="tutor-dashboard-body">
			<div class="tutor-dashboard-page">
				<?php
				// Get requested page from query string and sanitize.
				$dashboard_page = Input::get( 'dashboard-page', 'home' );

				// Whitelist allowed pages to avoid arbitrary file inclusion.
				$allowed_pages = array(
					'home',
					'courses',
					'notes',
					'discussions',
					'calendar',
				);

				$allowed_pages = (array) apply_filters( 'tutor_demo_dashboard_allowed_pages', $allowed_pages );

				if ( $dashboard_page && in_array( $dashboard_page, $allowed_pages, true ) ) {
					tutor_load_template( 'demo-components.dashboard.pages.' . $dashboard_page );
				} else {
					?>
					<div class="tutor-text-h3 tutor-color-black tutor-p-8">
						<?php esc_html_e( 'Welcome to TutorLMS Dashboard', 'tutor' ); ?>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<?php tutor_load_template( 'demo-components.dashboard.components.nav-mobile' ); ?>
</div>
