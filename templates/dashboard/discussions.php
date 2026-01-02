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
$replies       = Input::get( 'replies', 0, Input::TYPE_INT );
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
<div class="tutor-dashboard-discussions tutor-surface-l1 tutor-border tutor-rounded-2xl" x-data="tutorDiscussions()">
	<?php
	if ( $replies ) {
		$template = tutor()->path . 'templates/dashboard/discussions/qna-replies.php';
		if ( 'lesson-comments' === $current_tab ) {
			$template = tutor()->path . 'templates/dashboard/discussions/lesson-comment-replies.php';
		}
		require_once $template;
	} else {
		?>
		<div class="tutor-p-6 tutor-border-b">
			<?php Nav::make()->items( $page_nav_items )->render(); ?>
		</div>
		<div class="tutor-sm-border tutor-sm-rounded-2xl tutor-sm-mt-4">
			<?php
			$template = tutor()->path . 'templates/dashboard/discussions/qna-list.php';
			if ( 'lesson-comments' === $current_tab ) {
				$template = tutor()->path . 'templates/dashboard/discussions/lesson-comment-list.php';
			}

			require_once $template;
			?>
		</div>
		<?php
	}
	?>
</div>
