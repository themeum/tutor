<?php
/**
 * Base Template for Account
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Dashboard;
use Tutor\Helpers\UrlHelper;
use TUTOR\Input;

defined( 'ABSPATH' ) || exit;
?>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php
wp_head();

$subpage       = tutor_utils()->array_get( 'tutor_dashboard_sub_page', $wp_query->query_vars, 'profile' );
$account_pages = Dashboard::get_account_pages();
$page_data     = $account_pages[ $subpage ] ?? array();
$page_template = $page_data['template'] ?? '';

$back_url  = UrlHelper::back( tutor_utils()->tutor_dashboard_url() );
$close_url = tutor_utils()->tutor_dashboard_url();
?>
<div class="tutor-account-page-wrapper">
	<?php require_once $page_template; ?>
</div>
<?php wp_footer(); ?>
