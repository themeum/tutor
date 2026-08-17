<?php
/**
 * Base template for isolated dashboard quiz attempt pages.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Dashboard;
use TUTOR\Template;

$dashboard_page    = get_query_var( 'tutor_dashboard_page' );
$dashboard_subpage = get_query_var( 'tutor_dashboard_sub_page' );
$isolated_pages    = Dashboard::get_isolated_pages();
$page_meta         = Dashboard::get_page_meta_data( $dashboard_page, $dashboard_subpage, $isolated_pages );
$page_data         = $page_meta['page_data'];

$meta_title = $page_meta['meta_title'];
Dashboard::set_document_title( $meta_title );

$site_shell                 = Template::get_site_shell_data( Template::SITE_SHELL_CONTEXT_DASHBOARD );
$show_dashboard_site_header = $site_shell['show_site_header'];
$show_dashboard_site_footer = $site_shell['show_site_footer'];
$has_dashboard_site_shell   = $site_shell['has_site_shell'];
$theme_header_selector      = $site_shell['theme_header_selector'];

tutor_page_elements_header( $show_dashboard_site_header );

$page_template = $page_data['template'] ?? '';
$back_url      = tutor_utils()->tutor_dashboard_url();
$close_url     = $back_url;
?>
<div
	class="tutor-dashboard-isolated-page-wrapper<?php echo esc_attr( $has_dashboard_site_shell ? ' tutor-has-site-shell' : '' ); ?>"
	<?php if ( $has_dashboard_site_shell ) : ?>
		data-tutor-dashboard-site-shell
		data-tutor-theme-header-selector="<?php echo esc_attr( $theme_header_selector ); ?>"
	<?php endif; ?>
>
	<?php
	if ( $page_template && file_exists( $page_template ) ) {
		require_once $page_template;
	}
	?>
</div>
<?php
tutor_page_elements_footer( $show_dashboard_site_footer );
