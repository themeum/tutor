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
use Tutor\Components\Avatar;
use Tutor\Components\PreviewTrigger;
use Tutor\Helpers\UrlHelper;

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
		'id'  => $question->comment_ID,
	)
);
?>
<div 
	class="tutor-qna-card"
	x-data="{ 
		...tutorPopover({ placement: 'bottom-end' }),
		isUnread: <?php echo $is_unread ? 'true' : 'false'; ?>, 
		isArchived: <?php echo (int) tutor_utils()->array_get( 'tutor_qna_archived', $meta, 0 ) ? 'true' : 'false'; ?>,
		isSolved: <?php echo $is_solved ? 'true' : 'false'; ?>,
		isImportant: <?php echo $is_important ? 'true' : 'false'; ?>
	}"
	:class="{ 'unread': isUnread, 'active': open }"
	@tutor-qna-action-success.window="
		if ($event.detail.questionId === <?php echo (int) $question_id; ?>) {
			if ($event.detail.action === 'read') isUnread = !isUnread;
			if ($event.detail.action === 'archived') isArchived = !isArchived;
			if ($event.detail.action === 'solved') isSolved = !isSolved;
			if ($event.detail.action === 'important') isImportant = !isImportant;
		}
	"
>
	<?php
		Avatar::make()
			->src( tutor_utils()->get_user_avatar_url( $question->user_id ) )
			->size( Size::SIZE_32 )
			->render();
	?>
	<div class="tutor-qna-card-content">
		<div class="tutor-qna-card-top">
			<div class="tutor-qna-card-author"><?php echo esc_html( $question->comment_author ); ?></div>
			<div class="tutor-flex tutor-gap-2">
				<span class="tutor-text-subdued tutor-text-subdued tutor-flex-shrink-0">asked in</span>
				<?php PreviewTrigger::make()->id( $question->course_id )->render(); ?>
			</div>
		</div>
		<h6 class="tutor-qna-card-title"><?php echo wp_kses_post( $content ); ?></h6>
		<div class="tutor-flex tutor-items-center tutor-justify-between">
			<div class="tutor-qna-card-meta">
				<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-qna-card-meta-reply-button">
					<?php esc_html_e( 'Reply', 'tutor' ); ?>
				</a>
				<div class="tutor-flex tutor-items-center tutor-gap-2">
					<?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 20, 20 ); ?> 
					<?php echo esc_html( $question->answer_count ); ?>
				</div>

				<?php if ( $last_reply ) { ?>
				<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-ml-2">
					<?php
					Avatar::make()
						->src( tutor_utils()->get_user_avatar_url( $last_reply->user_id ) )
						->size( Size::SIZE_20 )
						->render();
					?>
					<div class="tutor-text-small"><?php echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $last_reply->comment_date_gmt ) ) ) ); //phpcs:ignore ?></div>
				</div>
				<?php } else { ?>
					<div class="tutor-text-small"><?php echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $question->comment_date_gmt ) ) ) ); //phpcs:ignore ?></div>
				<?php } ?>
			</div>
			<?php if ( ! $is_user_asker ) : ?>
			<div class="tutor-flex tutor-gap-2 tutor-sm-hidden">
				<button 
					class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-btn-icon tutor-text-subdued"
					@click="handleQnASingleAction(<?php echo (int) $question->comment_ID; ?>, 'solved')"
					:disabled="qnaSingleActionMutation?.isPending"
				>
					<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'solved' && currentQuestionId === <?php echo (int) $question_id; ?>">
						<?php tutor_utils()->render_svg_icon( Icon::SPINNER, 14, 14, array( 'class' => 'tutor-animate-spin' ) ); ?>
					</template>
					<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'solved' && currentQuestionId === <?php echo (int) $question_id; ?>)">
						<span class="tutor-flex">
							<template x-if="isSolved">
								<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_FILL, 16, 16, array( 'class' => 'tutor-icon-success-primary' ) ); ?>
							</template>
							<template x-if="!isSolved">
								<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_CIRCLE ); ?>
							</template>
						</span>
					</template>
				</button>
				<button 
					class="tutor-btn tutor-btn-link tutor-btn-x-small tutor-btn-icon tutor-text-subdued"
					@click="handleQnASingleAction(<?php echo (int) $question->comment_ID; ?>, 'important')"
					:disabled="qnaSingleActionMutation?.isPending"
				>
					<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'important' && currentQuestionId === <?php echo (int) $question_id; ?>">
						<?php tutor_utils()->render_svg_icon( Icon::SPINNER, 14, 14, array( 'class' => 'tutor-animate-spin' ) ); ?>
					</template>
					<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'important' && currentQuestionId === <?php echo (int) $question_id; ?>)">
						<span class="tutor-flex">
							<template x-if="isImportant">
								<?php tutor_utils()->render_svg_icon( Icon::BOOKMARK_FILL, 16, 16, array( 'class' => 'tutor-icon-exception4' ) ); ?>
							</template>
							<template x-if="!isImportant">
								<?php tutor_utils()->render_svg_icon( Icon::BOOKMARK ); ?>
							</template>
						</span>
					</template>
				</button>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="tutor-qna-card-actions">
		<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-sm-hidden">
			<?php esc_html_e( 'Reply', 'tutor' ); ?>
		</a>
		<div class="tutor-flex">
			<button 
				x-ref="trigger" 
				@click="toggle()" 
				class="tutor-btn tutor-btn-text tutor-btn-x-small tutor-btn-icon tutor-qna-card-actions-trigger">
				<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES ); ?>
			</button>

			<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
				<div class="tutor-popover-menu">
					<?php if ( ! $is_user_asker ) : ?>
					<button 
						class="tutor-popover-menu-item tutor-hidden tutor-sm-flex"
						@click="handleQnASingleAction(<?php echo (int) $question->comment_ID; ?>, 'solved')"
						:disabled="qnaSingleActionMutation?.isPending"
					>
						<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'solved' && currentQuestionId === <?php echo (int) $question_id; ?>">
							<?php tutor_utils()->render_svg_icon( Icon::SPINNER, 14, 14, array( 'class' => 'tutor-animate-spin' ) ); ?>
						</template>
						<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'solved' && currentQuestionId === <?php echo (int) $question_id; ?>)">
							<span class="tutor-flex">
								<template x-if="isSolved">
									<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_FILL, 16, 16, array( 'class' => 'tutor-icon-success-primary' ) ); ?>
								</template>
								<template x-if="!isSolved">
									<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_CIRCLE ); ?>
								</template>
							</span>
						</template>
						<?php esc_html_e( 'Solved', 'tutor' ); ?>
					</button>

					<button 
						class="tutor-popover-menu-item tutor-hidden tutor-sm-flex"
						@click="handleQnASingleAction(<?php echo (int) $question->comment_ID; ?>, 'important')"
						:disabled="qnaSingleActionMutation?.isPending"
					>
						<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'important' && currentQuestionId === <?php echo (int) $question_id; ?>">
							<?php tutor_utils()->render_svg_icon( Icon::SPINNER, 14, 14, array( 'class' => 'tutor-animate-spin' ) ); ?>
						</template>
						<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'important' && currentQuestionId === <?php echo (int) $question_id; ?>)">
							<span class="tutor-flex">
								<template x-if="isImportant">
									<?php tutor_utils()->render_svg_icon( Icon::BOOKMARK_FILL, 16, 16, array( 'class' => 'tutor-icon-exception4' ) ); ?>
								</template>
								<template x-if="!isImportant">
									<?php tutor_utils()->render_svg_icon( Icon::BOOKMARK ); ?>
								</template>
							</span>
						</template>
						<?php esc_html_e( 'Important', 'tutor' ); ?>
					</button>

					<button 
						class="tutor-popover-menu-item"
						@click="handleQnASingleAction(<?php echo (int) $question_id; ?>, 'archived')"
						:disabled="qnaSingleActionMutation?.isPending"
					>
						<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'archived' && currentQuestionId === <?php echo (int) $question_id; ?>">
							<?php tutor_utils()->render_svg_icon( Icon::SPINNER, 14, 14, array( 'class' => 'tutor-animate-spin' ) ); ?>
						</template>
						<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'archived' && currentQuestionId === <?php echo (int) $question_id; ?>)">
							<?php tutor_utils()->render_svg_icon( Icon::ARCHIVE_2 ); ?>
						</template>
						<span x-text="isArchived ? '<?php echo esc_js( __( 'Un-Archive', 'tutor' ) ); ?>' : '<?php echo esc_js( __( 'Archive', 'tutor' ) ); ?>'"></span>
					</button>
					<?php endif; ?>

					<button 
						class="tutor-popover-menu-item"
						@click="handleQnASingleAction(<?php echo esc_html( $question_id ); ?>, 'read', { context: '<?php echo esc_html( $context ); ?>' })"
						:disabled="qnaSingleActionMutation?.isPending"
					>
						<template x-if="qnaSingleActionMutation?.isPending && currentAction === 'read' && currentQuestionId === <?php echo (int) $question_id; ?>">
							<?php tutor_utils()->render_svg_icon( Icon::SPINNER, 14, 14, array( 'class' => 'tutor-animate-spin' ) ); ?>
						</template>
						<template x-if="!(qnaSingleActionMutation?.isPending && currentAction === 'read' && currentQuestionId === <?php echo (int) $question_id; ?>)">
							<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
						</template>
						<span x-text="isUnread ? '<?php echo esc_js( __( 'Mark as Read', 'tutor' ) ); ?>' : '<?php echo esc_js( __( 'Mark as Unread', 'tutor' ) ); ?>'"></span>
					</button>

					<button
						class="tutor-popover-menu-item tutor-sm-border-t"
						@click="hide(); TutorCore.modal.showModal('tutor-qna-delete-modal', { questionId: <?php echo esc_html( $question_id ); ?> });"
					>
						<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
						<?php esc_html_e( 'Delete', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
