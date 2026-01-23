<?php
/**
 * Lesson Comments Template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\ConfirmationModal;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use TUTOR\Input;
use TUTOR\Lesson;

defined( 'ABSPATH' ) || exit;

$user_id       = get_current_user_id();
$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page  = max( 1, Input::post( 'current_page', 0, Input::TYPE_INT ) );
$order_filter  = Input::get( 'order' ) ?? Input::post( 'order' ) ?? 'DESC';

$comments_list_args = array(
	'post_id' => $lesson_id,
	'parent'  => 0,
	'paged'   => $current_page,
	'number'  => $item_per_page,
	'order'   => $order_filter,
);

$comment_count_args = array(
	'post_id' => $lesson_id,
	'parent'  => 0,
	'count'   => true,
);

$comments_count = Lesson::get_comments( $comment_count_args );
$comment_list   = Lesson::get_comments( $comments_list_args );
?>
<div x-show="activeTab === 'comments'" x-cloak x-data="tutorLessonComments(<?php echo esc_js( $lesson_id ); ?>, <?php echo esc_js( $comments_count ); ?>)" class="tutor-tab-panel" role="tabpanel">
	<div class="tutor-p-6" x-data="{ focused: false }">
		<div class="tutor-text-medium tutor-font-semibold tutor-mb-4">
			<?php esc_html_e( 'Join The Conversation', 'tutor' ); ?>
		</div>

		<?php
		tutor_load_template(
			'learning-area.lesson.comment-form',
			array(
				'form_id'          => 'lesson-comment-form',
				'placeholder'      => __( 'Write your comment...', 'tutor' ),
				'submit_handler'   => 'createCommentMutation?.mutate({ ...data, comment_post_ID: ' . (int) $lesson_id . ', comment_parent: 0, order: currentOrder })',
				'cancel_handler'   => 'reset(); focused = false',
				'is_pending'       => 'createCommentMutation?.isPending',
				'hide_footer_init' => true,
			)
		);
		?>
	</div>

	<?php if ( ! empty( $comment_list ) ) : ?>
		<div 
			class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-t"
			:class="{ 'tutor-loading-spinner': isReloading }"
		>
			<div class="tutor-small tutor-text-secondary">
				<?php esc_html_e( 'Comments', 'tutor' ); ?>
				<span class="tutor-text-primary tutor-font-medium" x-text="'(' + totalComments + ')'"></span>
			</div>
			<?php
			Sorting::make()
				->order( $order_filter )
				->on_change( 'handleChangeOrder' )
				->bind_active_order( 'currentOrder' )
				->render();
			?>
		</div>
		<div x-ref="commentList" class="tutor-comments-list tutor-border-t">
			<?php
			tutor_load_template(
				'learning-area.lesson.comment-list',
				compact( 'comment_list', 'lesson_id', 'user_id' )
			);
			?>
		</div>
		<?php
		ConfirmationModal::make()
			->id( 'delete-comment-modal' )
			->title( 'Delete This Item?' )
			->message( 'This action cannot be undone.' )
			->icon( Icon::DELETE_2, 80, 80 )
			->mutation_state( 'deleteCommentMutation' )
			->confirm_handler( 'deleteCommentMutation?.mutate({ comment_id: payload?.commentId })' )
			->render();
		?>
	<?php endif; ?>

	<div x-ref="loadMoreTrigger" aria-hidden="true">
		<span x-show="loading" class="tutor-loading-spinner tutor-border-t"></span>
	</div>
</div>
