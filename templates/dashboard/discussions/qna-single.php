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
use Tutor\Components\Button;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\Constants\Size;
use Tutor\Components\PreviewTrigger;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use Tutor\Components\Constants\Variant;
use TUTOR\Input;
use TUTOR\User;

$question = tutor_utils()->get_qa_question( (int) $discussion_id );
if ( ! $question ) {
	wp_safe_redirect( $discussion_url );
	return;
}

$user_id             = get_current_user_id();
$is_user_asker       = $user_id === (int) $question->user_id;
$qna_delete_modal_id = 'tutor-qna-delete-modal';

$replies_order = Input::get( 'order', '' );
$replies       = tutor_utils()->get_qa_answer_by_question( $discussion_id, $replies_order, 'frontend' );

$is_solved    = (int) tutor_utils()->array_get( 'tutor_qna_solved', $question->meta, 0 );
$is_important = (int) tutor_utils()->array_get( 'tutor_qna_important', $question->meta, 0 );
$is_archived  = (int) tutor_utils()->array_get( 'tutor_qna_archived', $question->meta, 0 );

?>
<div class="tutor-discussion-single" x-init="isSolved = <?php echo $is_solved ? 'true' : 'false'; ?>; isImportant = <?php echo $is_important ? 'true' : 'false'; ?>; isArchived = <?php echo $is_archived ? 'true' : 'false'; ?>;">
	<div class="tutor-flex tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
		<a href="<?php echo esc_url( $discussion_url ); ?>" class="tutor-btn tutor-btn-secondary tutor-btn-small tutor-gap-2">
			<?php SvgIcon::make()->name( Icon::ARROW_LEFT_2 )->flip_rtl()->render(); ?>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
		<?php if ( User::is_instructor_view() ) : ?>
		<div class="tutor-flex tutor-gap-2">
			<button 
				class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-gap-2 tutor-text-subdued"
				@click="handleQnASingleAction(<?php echo esc_html( $question->comment_ID ); ?>, 'solved')"
				:disabled="qnaSingleActionMutation?.isPending"
			>
				<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'solved'">
					<?php SvgIcon::make()->name( Icon::SPINNER )->size( 14 )->attr( 'class', 'tutor-animate-spin' )->render(); ?>
				</template>
				<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'solved')">
					<span class="tutor-flex">
						<template x-if="isSolved">
							<?php SvgIcon::make()->name( Icon::COMPLETED_FILL )->size( 16 )->color( Color::SUCCESS_PRIMARY )->render(); ?>
						</template>
						<template x-if="!isSolved">
							<?php SvgIcon::make()->name( Icon::COMPLETED_CIRCLE )->render(); ?>
						</template>
					</span>
				</template>
				<?php esc_html_e( 'Solved', 'tutor' ); ?>
			</button>

			<button 
				class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-gap-2 tutor-text-subdued"
				@click="handleQnASingleAction(<?php echo esc_html( $question->comment_ID ); ?>, 'important')"
				:disabled="qnaSingleActionMutation?.isPending"
			>
				<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'important'">
					<?php SvgIcon::make()->name( Icon::SPINNER )->size( 14 )->attr( 'class', 'tutor-animate-spin' )->render(); ?>
				</template>
				<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'important')">
					<span class="tutor-flex">
						<template x-if="isImportant">
							<?php SvgIcon::make()->name( Icon::BOOKMARK_FILL )->size( 16 )->color( Color::EXCEPTION4 )->render(); ?>
						</template>
						<template x-if="!isImportant">
							<?php SvgIcon::make()->name( Icon::BOOKMARK )->render(); ?>
						</template>
					</span>
				</template>
				<?php esc_html_e( 'Important', 'tutor' ); ?>
			</button>
		</div>
		<?php endif; ?>
	</div>
	<div class="tutor-discussion-single-body tutor-p-6 tutor-border-b">
		<div class="tutor-flex tutor-gap-5 tutor-mb-5">
			<?php Avatar::make()->user( $question->user_id )->size( Size::SIZE_40 )->render(); ?>
			<div class="tutor-min-w-0 tutor-flex-1">
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-small">
					<span class="tutor-discussion-card-author tutor-flex-shrink-0"><?php echo esc_html( $question->comment_author ); ?></span> 
					<span class="tutor-text-secondary tutor-flex-shrink-0">
						<?php
							/* translators: %s human-readable time difference. */
							echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $question->comment_date_gmt ) ) ) );
						?>
					</span>
				</div>
				<div class="tutor-tiny tutor-flex tutor-items-center tutor-gap-1 tutor-overflow-hidden">
					<span class="tutor-text-subdued tutor-flex-shrink-0">asked in</span> 
					<div class="tutor-min-w-0 tutor-flex-1">
						<?php PreviewTrigger::make()->id( $question->course_id )->render(); ?>
					</div>
				</div>
			</div>
			<div class="tutor-ml-auto">
				<div x-data="tutorPopover({ placement: 'bottom-end', offset: 4 })" class="tutor-quiz-item-result-more">
					<?php
					Button::make()
						->label( __( 'More options', 'tutor' ) )
						->variant( Variant::GHOST )
						->size( Size::X_SMALL )
						->icon( Icon::ELLIPSES, 'left', Size::SIZE_16, Color::SECONDARY )
						->icon_only()
						->attr( 'x-ref', 'trigger' )
						->attr( '@click', 'toggle()' )
						->render();
					?>

					<div 
						x-ref="content"
						x-show="open"
						x-cloak
						@click.outside="handleClickOutside()"
						class="tutor-popover"
					>
						<div class="tutor-popover-menu" style="min-width: 130px;">
							<?php if ( User::is_instructor_view() ) : ?>
							<button 
								class="tutor-popover-menu-item tutor-gap-5"
								@click="handleQnASingleAction(<?php echo esc_html( $question->comment_ID ); ?>, 'archived')"
								:disabled="qnaSingleActionMutation?.isPending"
							>
								<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'archived'">
									<?php SvgIcon::make()->name( Icon::SPINNER )->size( 20 )->color( Color::SECONDARY )->attr( 'class', 'tutor-animate-spin' )->render(); ?>
								</template>
								<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'archived')">
									<?php SvgIcon::make()->name( Icon::ARCHIVE_2 )->size( 20 )->render(); ?> 
								</template>
								<span x-text="isArchived ? '<?php echo esc_js( __( 'Un-Archive', 'tutor' ) ); ?>' : '<?php echo esc_js( __( 'Archive', 'tutor' ) ); ?>'"></span>
							</button>
							<?php endif; ?>
							<?php if ( $is_user_asker ) : ?>
							<button class="tutor-popover-menu-item tutor-gap-5" @click="setEditing(<?php echo (int) $question->comment_ID; ?>, 'qna'); hide()">
								<?php SvgIcon::make()->name( Icon::EDIT_2 )->size( 20 )->render(); ?>
								<?php esc_html_e( 'Edit', 'tutor' ); ?>
							</button>
							<?php endif; ?>
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
		</div>

		<div x-show="editingId !== <?php echo (int) $question->comment_ID; ?>" id="tutor-qna-text-<?php echo esc_attr( $question->comment_ID ); ?>" class="tutor-p1 tutor-font-medium tutor-text-secondary">
			<?php echo wp_kses_post( $question->comment_content ); ?>
		</div>

		<?php if ( $is_user_asker ) : ?>
			<div x-show="editingId === <?php echo (int) $question->comment_ID; ?>" x-cloak class="tutor-w-full">
				<?php
					tutor_load_template(
						'dashboard.discussions.qna-form',
						array(
							'form_id'             => 'qna-edit-' . (int) $question->comment_ID,
							'default_value'       => $question->comment_content,
							'submit_handler'      => '(data) => updateQnAMutation?.mutate({ ...data, question_id: ' . (int) $question->comment_ID . ' })',
							'cancel_handler'      => 'setEditing(null)',
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
		'dashboard.discussions.qna-form',
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
			'dashboard.discussions.qna-replies',
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
