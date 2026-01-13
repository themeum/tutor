<?php
/**
 * Lesson comments list template.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\ConfirmationModal;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use TUTOR\Lesson;

$is_instructor = tutor_utils()->is_instructor( null, true );

$lesson_ids = get_posts(
	array(
		'post_type'      => tutor()->lesson_post_type,
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

$list_args = array(
	'post__in' => $lesson_ids,
	'status'   => 'approve',
	'parent'   => 0,
	'number'   => $item_per_page,
	'offset'   => $offset,
	'orderby'  => 'comment_date',
	'order'    => $order_filter,
);

if ( ! $is_instructor ) {
	$list_args['user_id'] = get_current_user_id();
}

$count_args = array(
	'post__in' => $lesson_ids,
	'status'   => 'approve',
	'parent'   => 0,
	'count'    => true,
);

if ( ! $is_instructor ) {
	$count_args['user_id'] = get_current_user_id();
}

$lesson_comments = Lesson::get_comments( $list_args );
$total_items     = Lesson::get_comments( $count_args );
?>

<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
	<div class="tutor-small tutor-text-secondary">
		<?php esc_html_e( 'Comments', 'tutor' ); ?>
		<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( $total_items ); ?>)</span>
	</div>
	<div class="tutor-qna-filter-right">
		<?php Sorting::make()->order( $order_filter )->render(); ?>
	</div>
</div>

<?php if ( empty( $lesson_comments ) ) : ?>
	<?php EmptyState::make()->title( 'No Comments Found!' )->render(); ?>
<?php else : ?>
<div class="tutor-flex tutor-flex-column tutor-gap-4 tutor-sm-gap-none tutor-p-6 tutor-sm-p-none">
	<?php
	foreach ( $lesson_comments as $lesson_comment ) :
		tutor_load_template(
			'dashboard.discussions.lesson-comment-card',
			array(
				'lesson_comment' => $lesson_comment,
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
if ( ! empty( $lesson_comments ) ) {
	ConfirmationModal::make()
		->id( 'tutor-comment-delete-modal' )
		->title( __( 'Delete This Comment?', 'tutor' ) )
		->message( __( 'Are you sure you want to delete this comment permanently? Please confirm your choice.', 'tutor' ) )
		->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
		->confirm_handler( 'handleDeleteComment(payload?.commentId)' )
		->mutation_state( 'deleteCommentMutation' )
		->render();
}

