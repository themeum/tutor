<?php
/**
 * Template for displaying frontend dashboard
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Dashboard;
use TUTOR\User;

$is_by_short_code = isset( $is_shortcode ) && true === $is_shortcode;
global $wp_query;

$dashboard_page_slug     = '';
$dashboard_page_sub_slug = '';
$dashboard_page_name     = '';

if ( isset( $wp_query->query_vars['tutor_dashboard_page'] ) && $wp_query->query_vars['tutor_dashboard_page'] ) {
	$dashboard_page_slug = $wp_query->query_vars['tutor_dashboard_page'];
	$dashboard_page_name = $wp_query->query_vars['tutor_dashboard_page'];
}
/**
 * Getting dashboard sub pages
 */
if ( isset( $wp_query->query_vars['tutor_dashboard_sub_page'] ) && $wp_query->query_vars['tutor_dashboard_sub_page'] ) {
	$dashboard_page_sub_slug = $wp_query->query_vars['tutor_dashboard_sub_page'];
	$dashboard_page_name     = $dashboard_page_sub_slug;
	if ( $dashboard_page_slug ) {
		$dashboard_page_name = $dashboard_page_slug . '/' . $dashboard_page_name;
	}
}
$dashboard_page_name = apply_filters( 'tutor_dashboard_sub_page_template', $dashboard_page_name );

$dashboard_pages = tutor_utils()->tutor_dashboard_nav_ui_items();
$page_title      = __( 'Dashboard', 'tutor' );

$format_slug_title = static function ( $slug ) {
	$slug = str_replace( array( '-', '_', '/' ), ' ', (string) $slug );
	return wp_strip_all_tags( ucwords( $slug ) );
};

if ( $dashboard_page_slug && isset( $dashboard_pages[ $dashboard_page_slug ] ) ) {
	$page_data  = $dashboard_pages[ $dashboard_page_slug ];
	$page_title = is_array( $page_data ) ? ( $page_data['title'] ?? $page_title ) : $page_data;
}

$page_meta = Dashboard::get_page_meta_data(
	$page_title,
	$page_data['meta_description'] ?? __( 'Tutor dashboard', 'tutor' )
);

$meta_title       = $page_meta['meta_title'];
$meta_description = $page_meta['meta_description'];
Dashboard::set_document_title( $meta_title );

if ( ! $is_by_short_code && ! defined( 'OTLMS_VERSION' ) ) :
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="title" content="<?php echo esc_attr( $meta_title ); ?>" />
		<meta name="description" content="<?php echo esc_attr( $meta_description ); ?>" />
		<?php wp_head(); ?>
	</head>
	<body <?php body_class( '' ); ?>>
		<?php wp_body_open(); ?>
	<?php
endif;

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
				} elseif ( User::is_instructor_view() ) {
						tutor_load_template( 'dashboard.dashboard' );
				} else {
					tutor_load_template( 'dashboard.student-dashboard' );
				}
				?>
			</div>
		</div>
	</div>
</div>
<?php do_action( 'tutor_dashboard/after/wrap' ); ?>
</body>
<?php if ( ! $is_by_short_code && ! defined( 'OTLMS_VERSION' ) ) : ?>
	</body>
	<?php wp_footer(); ?>
	</html>
<?php endif; ?>
</html>
