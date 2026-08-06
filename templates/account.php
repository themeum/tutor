<?php
/**
 * Base Template for Account
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Dashboard;

global $wp_query;

$subpage       = tutor_utils()->array_get( 'tutor_dashboard_sub_page', $wp_query->query_vars, 'profile' );
$account_pages = Dashboard::get_account_pages();
$page_meta     = Dashboard::get_page_meta_data( Dashboard::ACCOUNT_PAGE_SLUG, $subpage, $account_pages );
$page_data     = $page_meta['page_data'];
$page_template = $page_data['template'] ?? '';

$meta_title    = $page_meta['meta_title'];
$dashboard_url = tutor_utils()->tutor_dashboard_url();
$back_url      = apply_filters( 'tutor_dashboard_back_url', $dashboard_url );
$close_url     = $dashboard_url;

Dashboard::set_document_title( $meta_title );

$show_dashboard_site_header = (bool) tutor_utils()->get_option( 'show_dashboard_site_header' );
$show_dashboard_site_footer = (bool) tutor_utils()->get_option( 'show_dashboard_site_footer' );

tutor_page_elements_header( $show_dashboard_site_header );
?>
<div class="tutor-account-page-wrapper">
	<?php require_once $page_template; ?>
</div>
<?php
tutor_page_elements_footer( $show_dashboard_site_footer );
