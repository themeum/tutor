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

use Tutor\Components\ConfirmationModal;
use Tutor\Components\Pagination;
use Tutor\Components\Sorting;
use TUTOR\Input;

$is_instructor = tutor_utils()->is_instructor( null, true );
$view_option   = get_user_meta( get_current_user_id(), 'tutor_qa_view_as', true );
$q_status      = Input::get( 'data' );
$view_as       = $is_instructor ? ( $view_option ? $view_option : 'instructor' ) : 'student';
$asker_id      = 'instructor' === $view_as ? null : get_current_user_id();

$total_items = (int) tutor_utils()->get_qa_questions( $offset, $item_per_page, '', null, null, $asker_id, $q_status, true );
$questions   = tutor_utils()->get_qa_questions( $offset, $item_per_page, '', null, null, $asker_id, $q_status, false, array( 'order' => $order_filter ) );

?>

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
<div class="tutor-px-6 tutor-pb-6">
	<?php if ( $total_items > $item_per_page ) : ?>
		<?php Pagination::make()->current( $current_page )->total( $total_items )->limit( $item_per_page )->render(); ?>
	<?php endif; ?>
</div>

<?php
if ( ! empty( $questions ) ) {
	ConfirmationModal::make()
		->id( 'tutor-qna-delete-modal' )
		->title( __( 'Delete This Question?', 'tutor' ) )
		->message( __( 'Are you sure you want to delete this question permanently? Please confirm your choice.', 'tutor' ) )
		->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
		->confirm_handler( 'handleDeleteQnA(payload?.questionId)' )
		->mutation_state( 'deleteQnAMutation' )
		->render();
}
?>
