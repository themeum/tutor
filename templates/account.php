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
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( '' ); ?>>
	<?php wp_body_open(); ?>
<div class="tutor-account-page-wrapper">
	<?php require_once $page_template; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
