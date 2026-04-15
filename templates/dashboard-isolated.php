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
use Tutor\Helpers\UrlHelper;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php bloginfo( 'name' ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( '' ); ?>>
<?php

$dashboard_page    = get_query_var( 'tutor_dashboard_page' );
$dashboard_subpage = get_query_var( 'tutor_dashboard_sub_page' );
$page_key          = Dashboard::get_isolated_page_key( $dashboard_page, $dashboard_subpage );
$isolated_pages    = Dashboard::get_isolated_pages();
$page_data         = $isolated_pages[ $page_key ] ?? array();
$page_template     = $page_data['template'] ?? '';
$back_url          = tutor_utils()->tutor_dashboard_url();
$close_url         = $back_url;
?>
<div class="tutor-dashboard-isolated-page-wrapper">
	<?php
	if ( $page_template && file_exists( $page_template ) ) {
		require_once $page_template;
	}
	?>
</div>
<?php wp_footer(); ?>
</body>
</html>
