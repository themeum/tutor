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
use Tutor\Components\Pagination;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use TUTOR\Input;

defined( 'ABSPATH' ) || exit;

$current_url = admin_url( 'admin.php?page=playground&subpage=dashboard' );

$page_nav_items = array(
	array(
		'type'   => 'link',
		'label'  => __( 'Q&A', 'tutor' ),
		'icon'   => Icon::QA,
		'url'    => esc_url( add_query_arg( 'dashboard-page', 'qna', $current_url ) ),
		'active' => true,
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Lesson Comments', 'tutor' ),
		'icon'   => Icon::COMMENTS,
		'url'    => esc_url( add_query_arg( 'dashboard-page', 'lesson-comments', $current_url ) ),
		'active' => false,
	),
);

$order_filter  = Input::get( 'order', 'DESC' );
$is_instructor = tutor_utils()->is_instructor( null, true );
$view_option   = get_user_meta( get_current_user_id(), 'tutor_qa_view_as', true );
$q_status      = Input::get( 'data' );
$view_as       = $is_instructor ? ( $view_option ? $view_option : 'instructor' ) : 'student';
$asker_id      = 'instructor' === $view_as ? null : get_current_user_id();

$current_page  = max( 1, Input::get( 'current_page', 1, Input::TYPE_INT ) );
$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$offset        = ( $current_page - 1 ) * $item_per_page;

$total_items = tutor_utils()->get_qa_questions( $offset, $item_per_page, '', null, null, $asker_id, $q_status, true );
$questions   = tutor_utils()->get_qa_questions( $offset, $item_per_page, '', null, null, $asker_id, $q_status, false, array( 'order' => $order_filter ) );
?>
<div class="tutor-dashboard-discussions tutor-surface-l1 tutor-border tutor-rounded-2xl">
	<div class="tutor-p-6 tutor-border-b">
		<?php Nav::make()->items( $page_nav_items )->render(); ?>
	</div>
	<div class="tutor-sm-border tutor-sm-rounded-2xl tutor-sm-mt-4">
		<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
			<div class="tutor-small tutor-text-secondary">
				<?php esc_html_e( 'Questions', 'tutor' ); ?>
				<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( $total_items ); ?>)</span>
			</div>
			<div class="tutor-qna-filter-right">
				<?php Sorting::make()->order( $order_filter )->render(); ?>
			</div>
		</div>
		<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-p-6">
			<?php
			foreach ( $questions as $question ) :
				tutor_load_template(
					'demo-components.dashboard.components.qna-card',
					array(
						'question' => $question,
						'view_as'  => $view_as,
					)
				);
			endforeach;
			?>
		</div>
		<div class="tutor-px-6 tutor-pb-6">
			<?php if ( $total_items > $item_per_page ) : ?>
				<?php Pagination::make()->current( $current_page )->total( $total_items )->limit( $item_per_page )->render(); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
