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
$page_template = $page_data['page_template'];

$back_url = wp_get_referer();
if ( ! $back_url ) {
	$back_url = tutor_utils()->tutor_dashboard_url();
}
?>
<div class="tutor-user-reviews">
	<div class="tutor-profile-header">
		<div class="tutor-profile-container">
			<div class="tutor-flex tutor-items-center tutor-justify-between">
				<a href="<?php echo esc_url( $back_url ); ?>" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
					<?php tutor_utils()->render_svg_icon( Icon::LEFT ); ?>
				</a>
				<h4 class="tutor-profile-header-title">
					<?php echo esc_html( $page_data['title'] ?? '' ); ?>
				</h4>
				<a href="<?php echo esc_url( $back_url ); ?>" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
					<?php tutor_utils()->render_svg_icon( Icon::CROSS ); ?>
				</a>
			</div>
		</div>
	</div>
	<div class="tutor-profile-container">
		<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-mt-9">
			<?php require_once $page_template; ?>
		</div>
	</div>
</div>
<?php wp_footer(); ?>
