<?php
/**
 * Lesson comments template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Avatar;
use Tutor\Components\Button;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\InputField;
use Tutor\Components\Sorting;
use TUTOR\Icon;
use TUTOR\Input;
use TUTOR\Lesson;

defined( 'ABSPATH' ) || exit;

global $tutor_course_id;

$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page  = max( 1, Input::post( 'current_page', 0, Input::TYPE_INT ) );

$comments_list_args = array(
	'post_id' => $lesson_id,
	'parent'  => 0,
	'paged'   => $current_page,
	'number'  => $item_per_page,
);

$comment_count_args = array(
	'post_id' => $lesson_id,
	'parent'  => 0,
	'count'   => true,
);

$comments_count = Lesson::get_comments( $comment_count_args );
$comment_list   = Lesson::get_comments( $comments_list_args );
?>
<div x-show="activeTab === 'comments'" x-cloak x-data="tutorLessonComments()" class="tutor-tab-panel" role="tabpanel">
	<form 
		class="tutor-p-6" 
		x-data="{ ...tutorForm({ id: 'lesson-comment-form' }), focused: false }"
		x-bind="getFormBindings()"
		@submit.prevent="handleSubmit((data) => createCommentMutation?.mutate({ ...data, comment_post_ID: <?php echo esc_html( $lesson_id ); ?>, comment_parent: 0 }))($event)"
	>
		<div class="tutor-text-medium tutor-font-semibold tutor-mb-4">
			<?php esc_html_e( 'Join The Conversation', 'tutor' ); ?>
		</div>

		<?php
		InputField::make()
			->type( InputType::TEXTAREA )
			->name( 'comment' )
			->placeholder( __( 'Write your comment...', 'tutor' ) )
			->attr( 'x-bind', "register('comment', { required: '" . esc_js( __( 'Please enter a comment', 'tutor' ) ) . "' })" )
			->attr( '@focus', 'focused = true' )
			->attr( '@keydown', 'handleKeydown($event)' )
			->render();
		?>

		<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mt-5" x-cloak :class="{ 'tutor-hidden': !focused }">
			<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2">
				<?php tutor_utils()->render_svg_icon( Icon::COMMAND, 12, 12 ); ?> 
				<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
				<?php tutor_utils()->render_svg_icon( Icon::ENTER, 12, 12 ); ?> 
				<?php esc_html_e( 'Enter to Save	', 'tutor' ); ?>
			</div>
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<?php
				Button::make()
					->label( __( 'Cancel', 'tutor' ) )
					->variant( Variant::GHOST )
					->size( Size::X_SMALL )
					->attr( 'type', 'button' )
					->attr( '@click', 'reset(); focused = false' )
					->attr( ':disabled', 'createCommentMutation?.isPending' )
					->render();

				Button::make()
					->label( __( 'Save', 'tutor' ) )
					->variant( Variant::PRIMARY_SOFT )
					->size( Size::X_SMALL )
					->attr( 'type', 'submit' )
					->attr( ':disabled', 'createCommentMutation?.isPending' )
					->attr( ':class', "{ 'tutor-btn-loading': createCommentMutation?.isPending }" )
					->render();
				?>
			</div>
		</div>
	</form>
	<?php if ( ! empty( $comment_list ) ) : ?>
	<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-t">
		<div class="tutor-small tutor-text-secondary">
			<?php esc_html_e( 'Comments', 'tutor' ); ?>
			<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( $comments_count ); ?>)</span>
		</div>
		<?php Sorting::make()->order( Input::get( 'order', 'DESC' ) )->render(); ?>
	</div>
	<div class="tutor-comments-list tutor-border-t">
		<?php foreach ( $comment_list as $comment_item ) : ?>
			<div class="tutor-comment" x-data="{showReplyForm: false, repliesExpanded: false}">
				<?php Avatar::make()->user( $comment_item->user_id )->size( Size::SIZE_40 )->render(); ?>
				<div class="tutor-flex-1">
					<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
						<span class="tutor-discussion-card-author">
							<?php echo esc_html( $comment_item->comment_author ); ?>
						</span> 
						<span class="tutor-text-subdued">
							<?php
								// Translators: %s is the time of comment.
								echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $comment_item->comment_date_gmt ) ) ) );
							?>
						</span>
					</div>
					<div class="tutor-p2 tutor-text-secondary">
						<?php echo wp_kses_post( $comment_item->comment_content ); ?>
					</div>
					<div class="tutor-mt-6">
						<button class="tutor-comment-action-btn tutor-comment-action-btn-reply" @click="showReplyForm = !showReplyForm">
							<?php tutor_utils()->render_svg_icon( Icon::COMMENTS ); ?>
							<?php esc_html_e( 'Reply', 'tutor' ); ?>
						</button>
					</div>
					<form 
						class="tutor-comment-reply-form tutor-mt-6" 
						x-show="showReplyForm" 
						x-collapse
						x-data="{ ...tutorForm({ id: 'lesson-comment-reply-form' }), focused: false }"
						x-bind="getFormBindings()"
						@submit.prevent="handleSubmit((data) => replyCommentMutation?.mutate({ ...data, comment_post_ID: <?php echo esc_html( $lesson_id ); ?>, comment_parent: <?php echo esc_html( $comment_item->comment_ID ); ?> }))($event)"
					>
						<?php
						InputField::make()
							->type( InputType::TEXTAREA )
							->name( 'comment' )
							->placeholder( __( 'Write your reply', 'tutor' ) )
							->attr( 'x-bind', "register('comment', { required: '" . esc_js( __( 'Please enter a reply', 'tutor' ) ) . "' })" )
							->attr( '@focus', 'focused = true' )
							->attr( '@keydown', 'handleKeydown($event)' )
							->render();
						?>
						<div class="tutor-flex tutor-items-center tutor-justify-between tutor-mt-5" x-cloak :class="{ 'tutor-hidden': !focused }">
							<div class="tutor-tiny tutor-text-subdued tutor-flex tutor-items-center tutor-gap-2">
								<?php tutor_utils()->render_svg_icon( Icon::COMMAND, 12, 12 ); ?> 
								<?php esc_html_e( 'Cmd/Ctrl +', 'tutor' ); ?>
								<?php tutor_utils()->render_svg_icon( Icon::ENTER, 12, 12 ); ?> 
								<?php esc_html_e( 'Enter to Save	', 'tutor' ); ?>
							</div>
							<div class="tutor-flex tutor-items-center tutor-gap-4">
								<?php
								Button::make()
									->label( __( 'Cancel', 'tutor' ) )
									->variant( Variant::GHOST )
									->size( Size::X_SMALL )
									->attr( 'type', 'button' )
									->attr( '@click', 'reset(); focused = false; showReplyForm = false' )
									->attr( ':disabled', 'replyCommentMutation?.isPending' )
									->render();

								Button::make()
									->label( __( 'Save', 'tutor' ) )
									->variant( Variant::PRIMARY_SOFT )
									->size( Size::X_SMALL )
									->attr( 'type', 'submit' )
									->attr( ':disabled', 'replyCommentMutation?.isPending' )
									->attr( ':class', "{ 'tutor-btn-loading': replyCommentMutation?.isPending }" )
									->render();
								?>
							</div>
						</div>
					</form>

					<?php
					$replies     = Lesson::get_comments(
						array(
							'post_id' => $lesson_id,
							'parent'  => $comment_item->comment_ID,
						)
					);
					$reply_count = is_array( $replies ) ? count( $replies ) : 0;
					?>

					<?php if ( $reply_count > 0 ) : ?>
					<div class="tutor-comment-replies" x-show="repliesExpanded" x-collapse>
						<?php foreach ( $replies as $reply_item ) : ?>
							<div class="tutor-flex tutor-gap-5">
								<?php Avatar::make()->user( $reply_item->user_id )->size( Size::SIZE_40 )->render(); ?>
								<div class="tutor-flex-1">
									<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
										<span class="tutor-discussion-card-author">
											<?php echo esc_html( $reply_item->comment_author ); ?>
										</span> 
										<span class="tutor-text-subdued">
											<?php
												// Translators: %s is the time of comment.
												echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $reply_item->comment_date_gmt ) ) ) );
											?>
										</span>
									</div>
									<div class="tutor-p2 tutor-text-secondary">
										<?php echo wp_kses_post( $reply_item->comment_content ); ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<button 
						class="tutor-comment-replies-toggle"
						@click="repliesExpanded = !repliesExpanded"
						x-ref="repliesToggle"
					>
						<span x-show="repliesExpanded" x-cloak>
							<?php echo esc_html__( 'Collapse all replies', 'tutor' ); ?>
						</span>
						<span x-show="!repliesExpanded">
							<?php
								echo esc_html(
									sprintf(
										/* translators: %d: number of replies */
										_n( '%d more reply', '%d more replies', $reply_count, 'tutor' ),
										$reply_count
									)
								);
							?>
						</span>
					</button>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>
