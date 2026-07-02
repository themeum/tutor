<?php
/**
 * Single Q&A card for Q&A list.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
use Tutor\Components\Avatar;
use Tutor\Components\Button;
use Tutor\Components\Constants\Variant;
use Tutor\Components\PreviewTrigger;
use Tutor\Helpers\UrlHelper;
use TUTOR\User;

$current_user_id = get_current_user_id();
$is_user_asker   = $current_user_id === (int) $question->user_id;
$context         = 'frontend-dashboard-qna-table-' . $view_as;
$key_slug        = 'frontend-dashboard-qna-table-student' === $context ? '_' . $current_user_id : '';
$meta            = $question->meta;
$is_read         = (int) tutor_utils()->array_get( 'tutor_qna_read' . $key_slug, $meta, 0 );
$is_unread       = 0 === $is_read;
$text_mark_as    = $is_unread ? __( 'Mark as Read', 'tutor' ) : __( 'Mark as Unread', 'tutor' );
$is_solved       = (int) tutor_utils()->array_get( 'tutor_qna_solved', $meta, 0 );
$is_important    = (int) tutor_utils()->array_get( 'tutor_qna_important', $meta, 0 );

$limit   = 60;
$content = wp_strip_all_tags( $question->comment_content );
$content = strlen( $content ) > $limit ? substr( $content, 0, $limit ) . '...' : $content;

$question_id = $question->comment_ID;
$last_reply  = null;
$answers     = tutor_utils()->get_qa_answer_by_question( $question_id, 'DESC', 'frontend' );

if ( ! empty( $answers ) ) {
	$last_reply = $answers[0];
}

$single_url = UrlHelper::add_query_params(
	$discussion_url,
	array(
		'tab' => 'qna',
		'id'  => $question_id,
	)
);
?>
<div
	class="tutor-discussion-card tutor-flex-column"
	data-question-id="<?php echo esc_attr( (int) $question_id ); ?>"
	x-show="editingId !== <?php echo (int) $question_id; ?>"
	x-data="{ 
		...tutorPopover({ placement: 'bottom-end' }),
		isUnread: <?php echo $is_unread ? 'true' : 'false'; ?>, 
		isArchived: <?php echo tutor_utils()->array_get( 'tutor_qna_archived', $meta, 0 ) ? 'true' : 'false'; ?>,
		isSolved: <?php echo $is_solved ? 'true' : 'false'; ?>,
		isImportant: <?php echo $is_important ? 'true' : 'false'; ?>
	}"
	:class="{ 'unread': isUnread, 'active': open }"
	@tutor-qna-action-success.window="
		if ($event.detail.questionId === <?php echo esc_html( $question_id ); ?>) {
			if ($event.detail.action === 'read') isUnread = !isUnread;
			if ($event.detail.action === 'archived') isArchived = !isArchived;
			if ($event.detail.action === 'solved') isSolved = !isSolved;
			if ($event.detail.action === 'important') isImportant = !isImportant;
		}
	"
>
	<div class="tutor-flex tutor-gap-4 tutor-w-full">
		<?php Avatar::make()->user( $question->user_id )->size( Size::SIZE_32 )->render(); ?>
		<div class="tutor-discussion-card-content">
			<div class="tutor-discussion-card-top">
				<div class="tutor-discussion-card-author tutor-flex-shrink-0"><?php echo esc_html( $question->comment_author ); ?></div>
				<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-overflow-hidden">
					<span class="tutor-text-subdued tutor-text-subdued tutor-flex-shrink-0"><?php echo esc_html__( 'asked in', 'tutor' ); ?></span>
					<div class="tutor-min-w-0 tutor-flex-1">
						<?php PreviewTrigger::make()->id( $question->course_id )->render(); ?>
					</div>
				</div>
			</div>
			<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-discussion-card-title tutor-break-words" id="<?php echo esc_attr( 'tutor-qna-text-' . (int) $question_id ); ?>"><?php echo wp_kses_post( $content ); ?></a>
			<div class="tutor-flex tutor-items-center tutor-justify-between tutor-sm-mt-4">
				<div class="tutor-discussion-card-meta">
					<button 
						@click="toggleReply(<?php echo (int) $question_id; ?>)"
						class="tutor-discussion-card-meta-reply-button"
						type="button"
					>
						<?php esc_html_e( 'Reply', 'tutor' ); ?>
					</button>
					<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-flex tutor-items-center tutor-gap-2">
						<?php SvgIcon::make()->name( Icon::COMMENTS )->size( 20 )->render(); ?>
						<span class="tutor-discussion-card-reply-count tutor-text-subdued"><?php echo esc_html( $question->answer_count ); ?></span>
					</a>

					<?php if ( $last_reply ) { ?>
					<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-ml-2">
						<?php Avatar::make()->user( $last_reply->user_id )->size( Size::SIZE_20 )->render(); ?>
						<div class="tutor-text-small">
							<?php
								// translators: %s human-readable time difference.
								echo esc_html( sprintf( _x( '%s ago', 'human-readable time difference', 'tutor' ), human_time_diff( strtotime( $question->comment_date_gmt ) ) ) );
							?>
						</div>
					</div>
					<?php } else { ?>
						<div class="tutor-text-small">
							<?php
								/* translators: %s human-readable time difference. */
								echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $question->comment_date_gmt ) ) ) );
							?>
						</div>
					<?php } ?>
				</div>
				<?php if ( User::is_instructor_view() ) : ?>
				<div class="tutor-flex tutor-gap-2 tutor-sm-hidden">
					<div x-data="tutorTooltip({ placement: 'top', arrow: 'center' })" class="tutor-tooltip-wrap">
						<button 
							x-ref="trigger"
							class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon tutor-text-subdued"
							@click="handleQnASingleAction(<?php echo esc_html( $question_id ); ?>, 'solved')"
							:disabled="qnaSingleActionMutation?.isPending && currentAction === 'solved' && currentQuestionId === <?php echo esc_html( $question_id ); ?>"
							:aria-label="isSolved ? '<?php echo esc_js( __( 'Mark as Unresolved', 'tutor' ) ); ?>' : '<?php echo esc_js( __( 'Mark as Solved', 'tutor' ) ); ?>'"
						>
							<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'solved' && currentQuestionId === <?php echo esc_html( $question_id ); ?>">
								<?php SvgIcon::make()->name( Icon::SPINNER )->size( 14 )->attr( 'class', 'tutor-animate-spin' )->render(); ?>
							</template>
							<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'solved' && currentQuestionId === <?php echo esc_html( $question_id ); ?>)">
								<span class="tutor-flex">
									<template x-if="isSolved">
										<?php SvgIcon::make()->name( Icon::COMPLETED_FILL )->size( 16 )->color( Color::SUCCESS_PRIMARY )->render(); ?>
									</template>
									<template x-if="!isSolved">
										<?php SvgIcon::make()->name( Icon::COMPLETED_CIRCLE )->render(); ?>
									</template>
								</span>
							</template>
						</button>
						<div
							x-ref="content"
							x-show="open"
							x-cloak
							x-transition
							class="tutor-tooltip"
							x-text="isSolved
								? '<?php esc_html_e( 'Solved', 'tutor' ); ?>'
								: '<?php esc_html_e( 'Unresolved', 'tutor' ); ?>'"
							>
						</div>
					</div>
					<div x-data="tutorTooltip({ placement: 'top', arrow: 'center' })" class="tutor-tooltip-wrap">
						<button 
							x-ref="trigger"
							class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon tutor-text-subdued"
							@click="handleQnASingleAction(<?php echo esc_html( $question_id ); ?>, 'important')"
							:disabled="qnaSingleActionMutation?.isPending && currentAction === 'important' && currentQuestionId === <?php echo esc_html( $question_id ); ?>"
							:aria-label="isImportant ? '<?php echo esc_js( __( 'Mark as Not Important', 'tutor' ) ); ?>' : '<?php echo esc_js( __( 'Mark as Important', 'tutor' ) ); ?>'"
						>
							<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'important' && currentQuestionId === <?php echo esc_html( $question_id ); ?>">
								<?php SvgIcon::make()->name( Icon::SPINNER )->size( 14 )->attr( 'class', 'tutor-animate-spin' )->render(); ?>
							</template>
							<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'important' && currentQuestionId === <?php echo esc_html( $question_id ); ?>)">
								<span class="tutor-flex">
									<template x-if="isImportant">
										<?php SvgIcon::make()->name( Icon::BOOKMARK_FILL )->size( 16 )->color( Color::EXCEPTION4 )->render(); ?>
									</template>
									<template x-if="!isImportant">
										<?php SvgIcon::make()->name( Icon::BOOKMARK )->render(); ?>
									</template>
								</span>
							</template>
						</button>
						<div
							x-ref="content"
							x-show="open"
							x-cloak
							x-transition
							class="tutor-tooltip"
							x-text="isImportant
								? '<?php esc_html_e( 'This conversation is important', 'tutor' ); ?>'
								: '<?php esc_html_e( 'Mark this conversation as important', 'tutor' ); ?>'"
							>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="tutor-discussion-card-actions" x-show="replyingId !== <?php echo (int) $question_id; ?>" x-cloak>
			<?php
			Button::make()
				->variant( Variant::PRIMARY )
				->size( Size::X_SMALL )
				->label( __( 'Reply', 'tutor' ) )
				->size( Size::X_SMALL )
				->attr( '@click', 'toggleReply(' . (int) $question_id . ')' )
				->attr( 'class', 'tutor-force-sm-hidden' )
				->attr( 'type', 'button' )
				->render();
			?>
			<div class="tutor-flex">
				<?php
				Button::make()
					->label( __( 'More options', 'tutor' ) )
					->variant( Variant::SECONDARY )
					->size( Size::X_SMALL )
					->icon( Icon::ELLIPSES, 'left', Size::SIZE_16, Color::SECONDARY )
					->icon_only()
					->attr( 'x-ref', 'trigger' )
					->attr( '@click', 'toggle()' )
					->attr( 'class', 'tutor-discussion-card-actions-trigger' )
					->render();
				?>

				<div x-ref="content" x-show="open" x-transition.origin.right.top x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
					<div class="tutor-popover-menu">
						<?php if ( User::is_instructor_view() ) : ?>
						<button 
							class="tutor-popover-menu-item tutor-gap-5 tutor-force-hidden tutor-force-sm-flex"
							@click="handleQnASingleAction(<?php echo esc_html( $question_id ); ?>, 'solved')"
							:disabled="qnaSingleActionMutation?.isPending"
						>
							<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'solved' && currentQuestionId === <?php echo esc_html( $question_id ); ?>">
								<?php SvgIcon::make()->name( Icon::SPINNER )->size( 20 )->color( Color::SECONDARY )->attr( 'class', 'tutor-animate-spin' )->render(); ?>
							</template>
							<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'solved' && currentQuestionId === <?php echo esc_html( $question_id ); ?>)">
								<span class="tutor-flex">
									<template x-if="isSolved">
										<?php SvgIcon::make()->name( Icon::COMPLETED_FILL )->size( 20 )->color( Color::SUCCESS_PRIMARY )->render(); ?>
									</template>
									<template x-if="!isSolved">
										<?php SvgIcon::make()->name( Icon::COMPLETED_CIRCLE )->size( 20 )->render(); ?>
									</template>
								</span>
							</template>
							<?php esc_html_e( 'Solved', 'tutor' ); ?>
						</button>

						<button 
							class="tutor-popover-menu-item tutor-gap-5 tutor-force-hidden tutor-force-sm-flex"
							@click="handleQnASingleAction(<?php echo esc_html( $question_id ); ?>, 'important')"
							:disabled="qnaSingleActionMutation?.isPending"
						>
							<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'important' && currentQuestionId === <?php echo esc_html( $question_id ); ?>">
								<?php SvgIcon::make()->name( Icon::SPINNER )->size( 20 )->color( Color::SECONDARY )->attr( 'class', 'tutor-animate-spin' )->render(); ?>
							</template>
							<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'important' && currentQuestionId === <?php echo esc_html( $question_id ); ?>)">
								<span class="tutor-flex">
									<template x-if="isImportant">
										<?php SvgIcon::make()->name( Icon::BOOKMARK_FILL )->size( 20 )->color( Color::EXCEPTION4 )->render(); ?>
									</template>
									<template x-if="!isImportant">
										<?php SvgIcon::make()->name( Icon::BOOKMARK )->size( 20 )->render(); ?>
									</template>
								</span>
							</template>
							<?php esc_html_e( 'Important', 'tutor' ); ?>
						</button>

						<button 
							class="tutor-popover-menu-item tutor-gap-5"
							@click="handleQnASingleAction(<?php echo esc_html( $question_id ); ?>, 'archived')"
							:disabled="qnaSingleActionMutation?.isPending"
						>
							<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'archived' && currentQuestionId === <?php echo esc_html( $question_id ); ?>">
								<?php SvgIcon::make()->name( Icon::SPINNER )->size( 20 )->color( Color::SECONDARY )->attr( 'class', 'tutor-animate-spin' )->render(); ?>
							</template>
							<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'archived' && currentQuestionId === <?php echo esc_html( $question_id ); ?>)">
								<?php SvgIcon::make()->name( Icon::ARCHIVE_2 )->size( 20 )->render(); ?>
							</template>
							<span x-text="isArchived ? '<?php echo esc_js( __( 'Un-Archive', 'tutor' ) ); ?>' : '<?php echo esc_js( __( 'Archive', 'tutor' ) ); ?>'"></span>
						</button>
						<?php endif; ?>

						<button 
							class="tutor-popover-menu-item tutor-gap-5"
							@click="handleQnASingleAction(<?php echo esc_html( $question_id ); ?>, 'read', { context: '<?php echo esc_html( $context ); ?>' })"
							:disabled="qnaSingleActionMutation?.isPending"
						>
							<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'read' && currentQuestionId === <?php echo esc_html( $question_id ); ?>">
								<?php SvgIcon::make()->name( Icon::SPINNER )->size( 20 )->color( Color::SECONDARY )->attr( 'class', 'tutor-animate-spin' )->render(); ?>
							</template>
							<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'read' && currentQuestionId === <?php echo esc_html( $question_id ); ?>)">
								<span class="tutor-flex">
									<template x-if="isUnread">
										<?php SVGIcon::make()->name( Icon::READ )->size( 20 )->render(); ?>
									</template>
									<template x-if="!isUnread">
										<?php SVGIcon::make()->name( Icon::UNREAD )->size( 20 )->render(); ?>
									</template>
								</span>
							</template>
							<span x-text="isUnread ? '<?php echo esc_js( __( 'Mark as Read', 'tutor' ) ); ?>' : '<?php echo esc_js( __( 'Mark as Unread', 'tutor' ) ); ?>'"></span>
						</button>

						<?php if ( $is_user_asker ) : ?>
						<button class="tutor-popover-menu-item tutor-gap-5" @click="setEditing(<?php echo (int) $question_id; ?>, 'qna'); hide()">
							<?php SvgIcon::make()->name( Icon::EDIT_2 )->size( 20 )->render(); ?>
							<?php esc_html_e( 'Edit', 'tutor' ); ?>
						</button>
						<?php endif; ?>

						<button
							class="tutor-popover-menu-item tutor-gap-5 tutor-sm-border-t"
							@click="hide(); TutorCore.modal.showModal('tutor-qna-delete-modal', { question_id: <?php echo esc_html( $question_id ); ?> });"
						>
							<?php SvgIcon::make()->name( Icon::DELETE_2 )->size( 20 )->render(); ?>
							<?php esc_html_e( 'Delete', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div x-show="replyingId === <?php echo (int) $question_id; ?>" x-cloak class="tutor-card tutor-surface-l1-hover tutor-mt-4 tutor-w-full">
		<?php
		tutor_load_template(
			'dashboard.discussions.qna-form',
			array(
				'form_id'             => 'qna-reply-form-' . (int) $question_id,
				'submit_handler'      => '(data) => replyQnAMutation?.mutate({ ...data, question_id: ' . (int) $question_id . ', course_id: ' . (int) $question->course_id . ', reply_context: "list" })',
				'cancel_handler'      => 'setReplying(null)',
				'is_pending'          => 'replyQnAMutation?.isPending',
				'placeholder'         => __( 'Just drop your response here!', 'tutor' ),
				'label'               => __( 'Reply', 'tutor' ),
				'submit_label'        => __( 'Save', 'tutor' ),
				'keep_footer_visible' => true,
			)
		);
		?>
	</div>

	<?php if ( $is_user_asker ) : ?>
	<div x-show="editingId === <?php echo (int) $question_id; ?>" x-cloak class="tutor-card tutor-surface-l1-hover tutor-w-full">
		<?php
		tutor_load_template(
			'dashboard.discussions.qna-form',
			array(
				'form_id'             => 'qna-edit-' . (int) $question_id,
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
