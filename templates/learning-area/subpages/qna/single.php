<?php
/**
 * Q&A replies template.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use TUTOR\Input;

$question_id   = Input::get( 'question_id', 0, Input::TYPE_INT );
$replies_order = Input::get( 'order', '' );

$back_url = remove_query_arg( 'question_id' );

$question = tutor_utils()->get_qa_question( $question_id );
if ( ! $question ) {
	wp_safe_redirect( $back_url );
	return;
}

$user_id             = get_current_user_id();
$is_user_asker       = $user_id === (int) $question->user_id;
$qna_delete_modal_id = 'tutor-qna-delete-modal';

$replies = tutor_utils()->get_qa_answer_by_question( $question_id, $replies_order, 'frontend' );
?>
<div class="tutor-pt-4 tutor-pb-6">
	<div class="tutor-discussion-single" x-data="tutorQnA()">
		<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
			<a href="<?php echo esc_url( $back_url ); ?>" class="tutor-btn tutor-btn-secondary tutor-btn-small tutor-gap-2">
				<?php SvgIcon::make()->name( Icon::ARROW_LEFT_2 )->flip_rtl()->render(); ?>
				<?php esc_html_e( 'Back', 'tutor' ); ?>
			</a>
		</div>
		<div class="tutor-discussion-single-body tutor-p-6 tutor-border-b">
			<div class="tutor-flex tutor-gap-5 tutor-mb-5">
				<?php Avatar::make()->user( $question->user_id )->size( Size::SIZE_40 )->render(); ?>
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-small">
					<span class="tutor-discussion-card-author"><?php echo esc_html( $question->comment_author ); ?></span> 
					<span class="tutor-text-secondary">
						<?php
							/* translators: %s human-readable time difference. */
							echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $question->comment_date_gmt ) ) ) );
						?>
					</span>
				</div>
				<?php if ( $is_user_asker ) : ?>
				<div class="tutor-ml-auto">
					<div x-data="tutorPopover({ placement: 'bottom-end', offset: 4 })">
						<button class="tutor-btn tutor-btn-ghost tutor-btn-icon tutor-btn-x-small" x-ref="trigger" @click="toggle()" aria-label="<?php esc_attr_e( 'More options', 'tutor' ); ?>">
							<?php SvgIcon::make()->name( Icon::ELLIPSES )->size( 16 )->color( Color::SECONDARY )->render(); ?>
						</button>
						<div 
							x-ref="content"
							x-show="open"
							x-cloak
							@click.outside="handleClickOutside()"
							class="tutor-popover"
						>
							<div class="tutor-popover-menu" style="min-width: 130px;">
								<button class="tutor-popover-menu-item tutor-gap-5" @click="setEditing(<?php echo (int) $question->comment_ID; ?>); hide()">
									<?php SvgIcon::make()->name( Icon::EDIT_2 )->size( 20 )->render(); ?>
									<?php esc_html_e( 'Edit', 'tutor' ); ?>
								</button>
								<button 
									class="tutor-popover-menu-item tutor-gap-5"
									@click="TutorCore.modal.showModal('<?php echo esc_js( $qna_delete_modal_id ); ?>', { question_id: <?php echo esc_html( $question->comment_ID ); ?> }); hide();"
								>
									<?php SvgIcon::make()->name( Icon::DELETE_2 )->size( 20 )->render(); ?> <?php esc_html_e( 'Delete', 'tutor' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<div x-show="editingId !== <?php echo (int) $question->comment_ID; ?>" id="tutor-qna-text-<?php echo esc_attr( $question->comment_ID ); ?>" class="tutor-p1 tutor-font-medium tutor-text-secondary">
				<?php echo wp_kses_post( $question->comment_content ); ?>
			</div>

			<?php if ( $is_user_asker ) : ?>
				<div x-show="editingId === <?php echo (int) $question->comment_ID; ?>" x-cloak class="tutor-w-full">
					<?php
						tutor_load_template(
							'learning-area.subpages.qna.form',
							array(
								'form_id'             => 'qna-edit-' . (int) $question->comment_ID,
								'default_value'       => $question->comment_content,
								'submit_handler'      => '(data) => updateQnAMutation?.mutate({ ...data, question_id: ' . (int) $question->comment_ID . ' })',
								'cancel_handler'      => 'reset(); setEditing(null)',
								'is_pending'          => 'updateQnAMutation?.isPending',
								'keep_footer_visible' => true,
							)
						);
					?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		tutor_load_template(
			'learning-area.subpages.qna.form',
			array(
				'form_id'        => 'qna-reply-form-' . $question->comment_ID,
				'submit_handler' => '(data) => replyQnAMutation?.mutate({ ...data, question_id: ' . (int) $question->comment_ID . ', course_id: ' . (int) $question->course_id . ', reply_context: "single" })',
				'cancel_handler' => 'reset(); focused = false',
				'is_pending'     => 'replyQnAMutation?.isPending',
				'placeholder'    => __( 'Just drop your response here!', 'tutor' ),
				'label'          => __( 'Reply', 'tutor' ),
				'submit_label'   => __( 'Save', 'tutor' ),
				'form_class'     => 'tutor-discussion-single-reply-form tutor-p-6',
			)
		);
		?>

		<div id="tutor-discussion-replies-list">
			<?php
			tutor_load_template(
				'learning-area.subpages.qna.replies',
				array(
					'replies'       => $replies,
					'replies_order' => $replies_order,
					'user_id'       => $user_id,
				)
			);
			?>
		</div>

		<?php
		ConfirmationModal::make()
			->id( $qna_delete_modal_id )
			->title( __( 'Delete This Question?', 'tutor' ) )
			->message( __( 'Are you sure you want to delete this question permanently? Please confirm your choice.', 'tutor' ) )
			->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
			->confirm_handler( 'deleteQnAMutation?.mutate(payload)' )
			->mutation_state( 'deleteQnAMutation' )
			->render();
		?>
	</div>
</div>
