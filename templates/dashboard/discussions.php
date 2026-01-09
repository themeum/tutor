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

use Tutor\Components\Nav;
use TUTOR\Icon;
use TUTOR\Input;

defined( 'ABSPATH' ) || exit;

$current_tab   = Input::get( 'tab' );
$discussion_id = Input::get( 'id', 0, Input::TYPE_INT );
$order_filter  = Input::get( 'order', 'DESC' );
$current_page  = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$offset        = ( $current_page - 1 ) * $item_per_page;

$discussion_url = tutor_utils()->tutor_dashboard_url( 'discussions' );
$page_nav_items = array(
	array(
		'type'   => 'link',
		'label'  => __( 'Q&A', 'tutor' ),
		'icon'   => Icon::QA,
		'url'    => esc_url( $discussion_url ),
		'active' => 'qna' === $current_tab || empty( $current_tab ),
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Lesson Comments', 'tutor' ),
		'icon'   => Icon::COMMENTS,
		'url'    => esc_url( add_query_arg( 'tab', 'lesson-comments', $discussion_url ) ),
		'active' => 'lesson-comments' === $current_tab,
	),
);
?>
<div class="tutor-dashboard-discussions" x-data="tutorDiscussions()">
	<?php
	if ( $discussion_id ) {
		$template = tutor()->path . 'templates/dashboard/discussions/qna-single.php';
		if ( 'lesson-comments' === $current_tab ) {
			$template = tutor()->path . 'templates/dashboard/discussions/lesson-comment-replies.php';
		}
		require_once $template;
	} else {
		?>
		<div class="tutor-dashboard-page-card">
			<div class="tutor-dashboard-page-card-header">
				<?php Nav::make()->items( $page_nav_items )->render(); ?>
			</div>
			<div class="tutor-dashboard-page-card-body">
				<?php
				$template = tutor()->path . 'templates/dashboard/discussions/qna-list.php';
				if ( 'lesson-comments' === $current_tab ) {
					$template = tutor()->path . 'templates/dashboard/discussions/lesson-comment-list.php';
				}

				require_once $template;
				?>
			</div>
		</div>
		<?php
	}
	?>
</div>
