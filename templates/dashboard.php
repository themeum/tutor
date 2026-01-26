<?php
/**
 * Template for displaying frontend dashboard
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

use TUTOR\User;

$is_by_short_code = isset( $is_shortcode ) && true === $is_shortcode;
if ( ! $is_by_short_code && ! defined( 'OTLMS_VERSION' ) ) {
	?>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php
	wp_head();
}

global $wp_query;

$dashboard_page_slug = '';
$dashboard_page_name = '';
if ( isset( $wp_query->query_vars['tutor_dashboard_page'] ) && $wp_query->query_vars['tutor_dashboard_page'] ) {
	$dashboard_page_slug = $wp_query->query_vars['tutor_dashboard_page'];
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_page'];
}
/**
 * Getting dashboard sub pages
 */
if ( isset( $wp_query->query_vars['tutor_dashboard_sub_page'] ) && $wp_query->query_vars['tutor_dashboard_sub_page'] ) {
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_sub_page'];
	if ( $dashboard_page_slug ) {
		$dashboard_page_name = $dashboard_page_slug . '/' . $dashboard_page_name;
	}
}
$dashboard_page_name = apply_filters( 'tutor_dashboard_sub_page_template', $dashboard_page_name );

$user_id                   = get_current_user_id();
$user                      = get_user_by( 'ID', $user_id );
$enable_profile_completion = tutor_utils()->get_option( 'enable_profile_completion' );
$is_instructor             = tutor_utils()->is_instructor();

// URLS.
$current_url  = tutor()->current_url;
$footer_url_1 = trailingslashit( tutor_utils()->tutor_dashboard_url( $is_instructor ? 'my-courses' : '' ) );
$footer_url_2 = trailingslashit( tutor_utils()->tutor_dashboard_url( $is_instructor ? 'question-answer' : 'my-quiz-attempts' ) );

// Footer links.
$footer_links = array(
	array(
		'title'      => $is_instructor ? __( 'My Courses', 'tutor' ) : __( 'Dashboard', 'tutor' ),
		'url'        => $footer_url_1,
		'is_active'  => $footer_url_1 == $current_url,
		'icon_class' => 'ttr tutor-icon-dashboard',
	),
	array(
		'title'      => $is_instructor ? __( 'Q&A', 'tutor' ) : __( 'Quiz Attempts', 'tutor' ),
		'url'        => $footer_url_2,
		'is_active'  => $footer_url_2 == $current_url,
		'icon_class' => $is_instructor ? 'ttr  tutor-icon-question' : 'ttr tutor-icon-quiz-attempt',
	),
	array(
		'title'      => __( 'Menu', 'tutor' ),
		'url'        => '#',
		'is_active'  => false,
		'icon_class' => 'ttr tutor-icon-hamburger-o tutor-dashboard-menu-toggler',
	),
);

?>

<?php do_action( 'tutor_dashboard/before/wrap' ); ?>
<div class="tutor-dashboard-layout">
	<?php tutor_load_template( 'dashboard.components.sidebar' ); ?>
	<div class="tutor-dashboard-main">
		<?php tutor_load_template( 'dashboard.components.header' ); ?>
		<div class="tutor-dashboard-body">
			<div class="tutor-dashboard-page">
				<?php
				if ( $dashboard_page_name ) {
					do_action( 'tutor_load_dashboard_template_before', $dashboard_page_name );

					/**
					 * Load dashboard template part from other location
					 *
					 * This filter is basically added for adding templates from respective addons
					 *
					 * @since version 1.9.3
					 */
					$other_location      = '';
					$from_other_location = apply_filters( 'load_dashboard_template_part_from_other_location', $other_location );

					if ( '' == $from_other_location ) {
						tutor_load_template( 'dashboard.' . $dashboard_page_name );
					} else {
						// Load template from other location full abspath.
						include_once $from_other_location;
					}

					do_action( 'tutor_load_dashboard_template_after', $dashboard_page_name );
				} else {
					if ( User::is_instructor_view() ) {
						tutor_load_template( 'dashboard.dashboard' );
					} else {
						tutor_load_template( 'dashboard.student-dashboard' );
					}
				}
				?>
			</div>
		</div>
	</div>
	<?php tutor_load_template( 'demo-components.dashboard.components.nav-mobile' ); ?>
</div>
<?php do_action( 'tutor_dashboard/after/wrap' ); ?>
<?php
if ( ! $is_by_short_code && ! defined( 'OTLMS_VERSION' ) ) {
	wp_footer();
}



