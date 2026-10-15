<?php
/**
 * Discussions Page
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\Size;
use Tutor\Components\Nav;
use Tutor\Helpers\QueryHelper;
use Tutor\Helpers\UrlHelper;
use TUTOR\Icon;
use TUTOR\Input;
use TUTOR\Lesson;
use TUTOR\Q_And_A;

if ( ! Q_And_A::is_enabled() && ! Lesson::is_comment_enabled() ) {
	return;
}

$current_tab   = Input::get( 'tab' );
$discussion_id = Input::get( 'id', 0, Input::TYPE_INT );
$order_filter  = QueryHelper::get_valid_sort_order( Input::get( 'order', 'DESC' ) );
$current_page  = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$offset        = ( $current_page - 1 ) * $item_per_page;

$discussion_url = tutor_utils()->tutor_dashboard_url( 'discussions' );
$page_nav_items = array();

if ( Q_And_A::is_enabled() ) {
	$page_nav_items[] = array(
		'type'   => 'link',
		'label'  => __( 'Q&A', 'tutor' ),
		'icon'   => Icon::QA,
		'url'    => esc_url( $discussion_url ),
		'active' => 'qna' === $current_tab || empty( $current_tab ),
	);
}

if ( Lesson::is_comment_enabled() ) {
	$page_nav_items[] = array(
		'type'   => 'link',
		'label'  => __( 'Lesson Comments', 'tutor' ),
		'icon'   => Icon::COMMENTS,
		'url'    => UrlHelper::add_query_params( $discussion_url, array( 'tab' => 'lesson-comments' ) ),
		'active' => 'lesson-comments' === $current_tab,
	);
}

$number_of_tabs = tutor_utils()->count( $page_nav_items );
if ( $number_of_tabs <= 0 ) {
	return;
}

if ( 1 === $number_of_tabs ) {
	$page_nav_items[0]['active'] = true;
}
?>
<div class="tutor-dashboard-discussions" x-data="tutorDiscussions()">
	<h4 class="tutor-h4 tutor-mb-5 tutor-hidden tutor-sm-block">
		<?php esc_html_e( 'Discussions', 'tutor' ); ?>
	</h4>

	<?php
	if ( $discussion_id ) {
		$template = tutor()->path . 'templates/dashboard/discussions/qna-single.php';
		if ( 'lesson-comments' === $current_tab && Lesson::is_comment_enabled() ) {
			$template = tutor()->path . 'templates/dashboard/discussions/comment-single.php';
		}
		require_once $template;
	} else {
		?>
		<div class="tutor-dashboard-page-card">
			<div class="tutor-dashboard-page-card-header">
				<?php Nav::make()->items( $page_nav_items )->size( Size::SMALL )->render(); ?>
			</div>
			<div class="tutor-dashboard-page-card-body">
				<?php
				$template = tutor()->path . 'templates/dashboard/discussions/qna-list.php';
				if ( 'lesson-comments' === $current_tab && Lesson::is_comment_enabled() ) {
					$template = tutor()->path . 'templates/dashboard/discussions/comment-list.php';
				}

				require_once $template;
				?>
			</div>
		</div>
		<?php
	}
	?>
</div>
