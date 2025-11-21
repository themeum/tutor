<?php
/**
 * Tutor comment component.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 *
 * @var array $comment Comment data (may include 'replies' array).
 * @var bool  $is_reply Whether this is a reply comment. Default false.
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;

if ( empty( $comment ) ) {
	return;
}

// Extract replies from comment data.
$replies        = isset( $comment['replies'] ) && is_array( $comment['replies'] ) ? $comment['replies'] : array();
$has_replies    = ! empty( $replies );
$handle_save_js = "handleSaveReply() { const textarea = this.\$el.querySelector('.tutor-text-area'); if (textarea && textarea.value.trim()) { console.log('Saving reply:', textarea.value); textarea.value = ''; this.showReplyForm = false; } }";
$replies_state  = $has_replies
	? "{ showReplyForm: false, repliesExpanded: false, {$handle_save_js} }"
	: "{ showReplyForm: false, {$handle_save_js} }";
$is_reply       = isset( $is_reply ) && $is_reply;
?>
<div class="tutor-comment <?php echo $is_reply ? 'tutor-comment-reply' : ''; ?>" x-data="<?php echo esc_attr( $replies_state ); ?>">
	<div class="tutor-comment-avatar">
		<div class="tutor-avatar <?php echo $is_reply ? 'tutor-avatar-sm' : 'tutor-avatar-base'; ?>">
			<?php if ( ! empty( $comment['avatar'] ) ) : ?>
				<img src="<?php echo esc_url( $comment['avatar'] ); ?>" alt="<?php echo esc_attr( $comment['author'] ); ?>" class="tutor-avatar-image">
			<?php else : ?>
				<span class="tutor-avatar-initials">
					<?php echo esc_html( strtoupper( substr( $comment['author'], 0, 2 ) ) ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( $has_replies && ! $is_reply ) : ?>
		<div 
			class="tutor-comment-connector-line"
			:class="{ 'tutor-comment-connector-line-expanded': repliesExpanded }"
			x-data="tutorCommentConnectorLine()"
		></div>
	<?php endif; ?>
	<div class="tutor-comment-content">
		<div class="tutor-comment-header">
			<span class="tutor-comment-author"><?php echo esc_html( $comment['author'] ); ?></span>
			<span class="tutor-comment-separator">•</span>
			<span class="tutor-comment-time"><?php echo esc_html( $comment['time'] ); ?></span>
			<button class="tutor-comment-action-btn tutor-comment-action-btn-menu">
				<?php tutor_utils()->render_svg_icon( Icon::THREE_DOTS_VERTICAL, 16, 16 ); ?>
			</button>
		</div>
		<div class="tutor-comment-text">
			<?php echo wp_kses_post( $comment['text'] ); ?>
		</div>
		<div class="tutor-comment-actions">
			<button class="tutor-comment-action-btn tutor-comment-action-btn-like <?php echo ! empty( $comment['liked'] ) ? 'tutor-comment-action-btn-active' : ''; ?>">
				<?php tutor_utils()->render_svg_icon( ! empty( $comment['liked'] ) ? Icon::THUMB_FILL : Icon::THUMB, 16, 16 ); ?>
				<span><?php echo esc_html( $comment['likes'] ?? 0 ); ?></span>
			</button>
			<button class="tutor-comment-action-btn tutor-comment-action-btn-reply">
				<?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 16, 16 ); ?>
			</button>
			<button class="tutor-comment-action-btn tutor-comment-action-btn-reply" @click="showReplyForm = !showReplyForm">
				<?php esc_html_e( 'Reply', 'tutor' ); ?>
			</button>
		</div>

		<!-- Reply Form -->
		<div class="tutor-comment-reply-form" x-show="showReplyForm" x-collapse>
			<div class="tutor-input-field">
				<div class="tutor-input-wrapper">
					<textarea 
						type="text"
						placeholder="<?php esc_attr_e( 'Write your reply', 'tutor' ); ?>"
						class="tutor-text-area"
						rows="3"
						@keydown.meta.enter.prevent="handleSaveReply()"
						@keydown.ctrl.enter.prevent="handleSaveReply()"
					></textarea>
				</div>
			</div>
			<div class="tutor-comment-reply-actions">
				<span class="tutor-comment-keyboard-hint">
					<?php esc_html_e( '⌘ Cmd/Ctrl + ↵ Enter to Save', 'tutor' ); ?>
				</span>
				<div class="tutor-comment-reply-buttons">
					<button 
						type="button" 
						class="tutor-btn tutor-btn-outline-primary tutor-btn-sm"
						@click="showReplyForm = false"
					>
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
					<button 
						type="button" 
						class="tutor-btn tutor-btn-primary-soft tutor-btn-sm"
						@click="handleSaveReply()"
					>
						<?php esc_html_e( 'Save', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>

		<?php if ( $has_replies && ! $is_reply ) : ?>
			<?php
			$replies_count = count( $replies );
			/* translators: %d: number of replies */
			$replies_text  = sprintf( _n( '%d more reply', '%d more replies', $replies_count, 'tutor' ), $replies_count );
			$collapse_text = __( 'Collapse all replies', 'tutor' );
			?>
			<div class="tutor-comment-replies-wrapper">
				<!-- Replies Toggle -->
				<button 
					class="tutor-comment-replies-toggle"
					@click="repliesExpanded = !repliesExpanded"
					x-show="!repliesExpanded"
					x-ref="repliesToggle"
				>
					<span class="tutor-comment-replies-count"><?php echo esc_html( $replies_text ); ?></span>
				</button>

				<!-- Nested Replies -->
				<div class="tutor-comment-replies" x-show="repliesExpanded" x-collapse>
					<?php foreach ( $replies as $tutor_reply ) : ?>
						<?php
						tutor_load_template(
							'demo-components.learning-area.components.comments',
							array(
								'comment'  => $tutor_reply,
								'is_reply' => true,
							)
						);
						?>
					<?php endforeach; ?>
					
					<!-- Collapse button at bottom -->
					<button 
						class="tutor-comment-replies-collapse"
						@click="repliesExpanded = false"
						x-ref="repliesCollapse"
					>
						<span><?php echo esc_html( $collapse_text ); ?></span>
					</button>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
