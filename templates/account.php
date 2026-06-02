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
$page_data     = $account_pages[ $subpage ] ?? array();
$page_template = $page_data['template'] ?? '';
$page_title    = $page_data['title'] ?? __( 'Account', 'tutor' );

$page_meta = Dashboard::get_page_meta_data(
	$page_title,
	Dashboard::META_CONTEXT_ACCOUNT
);

$page_title       = $page_meta['page_title'];
$meta_title       = $page_meta['meta_title'];
$meta_description = $page_meta['meta_description'];

$dashboard_url = tutor_utils()->tutor_dashboard_url();
$back_url      = apply_filters( 'tutor_dashboard_back_url', $dashboard_url );
$close_url     = $dashboard_url;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( $page_title ); ?></title>
	<meta name="title" content="<?php echo esc_attr( $meta_title ); ?>" />
	<meta name="description" content="<?php echo esc_attr( $meta_description ); ?>" />
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
