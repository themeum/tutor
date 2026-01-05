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
use TUTOR\Icon;
use TUTOR\Input;

defined( 'ABSPATH' ) || exit;

wp_head();

$subpage       = Input::get( Dashboard::ACCOUNT_PAGE_QUERY_PARAM, 'profile' );
$account_pages = Dashboard::get_account_pages();
$page_data     = $account_pages[ $subpage ];
$page_template = $page_data['template'];

$back_url = wp_get_referer();
if ( ! $back_url ) {
	$back_url = tutor_utils()->tutor_dashboard_url();
}
?>
<div class="tutor-account-page-wrapper">
	<?php require_once $page_template; ?>
</div>
<?php wp_footer(); ?>
