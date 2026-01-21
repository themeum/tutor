<?php
/**
 * Q&A list template.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\ConfirmationModal;
use Tutor\Components\DropdownFilter;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\Sorting;
use TUTOR\Input;
use TUTOR\User;

$user_id       = get_current_user_id();
$is_instructor = tutor_utils()->is_instructor( $user_id, true );
$q_status      = Input::get( 'data' );
$view_as       = User::get_current_view_mode();
$asker_id      = User::is_instructor_view() ? null : $user_id;

$total_items = (int) tutor_utils()->get_qa_questions( $offset, $item_per_page, '', null, null, $asker_id, $q_status, true );
$questions   = tutor_utils()->get_qa_questions( $offset, $item_per_page, '', null, null, $asker_id, $q_status, false, array( 'order' => $order_filter ) );

$nav_items = array(
	array(
		'label' => __( 'All', 'tutor' ),
		'value' => '',
		'count' => $total_items,
	),
	array(
		'label' => __( 'Read', 'tutor' ),
		'value' => 'read',
		'count' => tutor_utils()->get_qa_questions( $offset, $item_per_page, '', null, null, $asker_id, 'read', true ),
	),
	array(
		'label' => __( 'Unread', 'tutor' ),
		'value' => 'unread',
		'count' => tutor_utils()->get_qa_questions( $offset, $item_per_page, '', null, null, $asker_id, 'unread', true ),
	),
	...( User::is_instructor_view() ? array(
		array(
			'label' => __( 'Important', 'tutor' ),
			'value' => 'important',
			'count' => tutor_utils()->get_qa_questions( $offset, $item_per_page, '', null, null, $asker_id, 'important', true ),
		),
		array(
			'label' => __( 'Archived', 'tutor' ),
			'value' => 'archived',
			'count' => tutor_utils()->get_qa_questions( $offset, $item_per_page, '', null, null, $asker_id, 'archived', true ),
		),
	) : array() ),
);
?>
<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-sm-p-5 tutor-border-b">
	<?php DropdownFilter::make()->options( $nav_items )->query_param( 'data' )->render(); ?>
	<div class="tutor-discussion-filter-right">
		<?php Sorting::make()->order( $order_filter )->render(); ?>
	</div>
</div>

<?php if ( empty( $questions ) ) : ?>
	<?php EmptyState::make()->title( 'No Questions Found!' )->render(); ?>
<?php else : ?>
<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-sm-gap-none tutor-p-6 tutor-sm-p-none">
	<?php
	foreach ( $questions as $question ) :
		tutor_load_template(
			'dashboard.discussions.qna-card',
			array(
				'question'       => $question,
				'view_as'        => $view_as,
				'discussion_url' => $discussion_url,
			)
		);
	endforeach;
	?>
</div>
<?php endif; ?>

<?php
Pagination::make()
	->current( $current_page )
	->total( $total_items )
	->limit( $item_per_page )
	->attr( 'class', 'tutor-px-6 tutor-pb-6 tutor-sm-p-5 tutor-sm-border-t' )
	->render();
?>

<?php
if ( ! empty( $questions ) ) {
	ConfirmationModal::make()
		->id( 'tutor-qna-delete-modal' )
		->title( __( 'Delete This Question?', 'tutor' ) )
		->message( __( 'Are you sure you want to delete this question permanently? Please confirm your choice.', 'tutor' ) )
		->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
		->confirm_handler( 'deleteQnAMutation?.mutate({ question_id: payload?.questionId })' )
		->mutation_state( 'deleteQnAMutation' )
		->render();
}
?>
