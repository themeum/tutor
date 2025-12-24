<?php
/**
 * Tutor dashboard Q&A card.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use Tutor\Components\Avatar;

$context   = 'frontend-dashboard-qna-table-' . $view_as;
$key_slug  = 'frontend-dashboard-qna-table-student' === $context ? '_' . $current_user_id : '';
$meta      = $question->meta;
$is_read   = (int) tutor_utils()->array_get( 'tutor_qna_read' . $key_slug, $meta, 0 );
$is_unread = 0 === $is_read;

$last_reply = null;
$answers    = tutils()->get_qa_answer_by_question( $question->comment_ID, 'DESC' );

if ( ! empty( $answers ) ) {
	$last_reply = $answers[0];
}

?>
<div class="tutor-qna-card <?php echo esc_attr( $is_unread ? 'unread' : '' ); ?>">
	<?php
		Avatar::make()
			->src( tutor_utils()->get_user_avatar_url( $question->user_id ) )
			->size( Size::SIZE_32 )
			->render();
	?>
	<div class="tutor-qna-card-content">
		<div class="tutor-qna-card-top">
			<div class="tutor-qna-card-author"><?php echo esc_html( $question->comment_author ); ?></div>
			<div>
				<span class="tutor-text-subdued">asked in</span> 
				<div class="tutor-preview-trigger"><?php echo esc_html( $question->post_title ); ?></div>
			</div>
		</div>
		<h6 class="tutor-qna-card-title"><?php echo wp_kses_post( $question->comment_content ); ?></h6>
		<div class="tutor-qna-card-meta">
			<button class="tutor-qna-card-meta-reply-button"><?php esc_html_e( 'Reply', 'tutor' ); ?></button>
			<div class="tutor-flex tutor-items-center tutor-gap-2"><?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 20, 20 ); ?> <?php echo esc_html( $question->answer_count ); ?></div>

			<?php if ( $last_reply ) : ?>
			<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-ml-2">
				<?php
				Avatar::make()
					->src( tutor_utils()->get_user_avatar_url( $last_reply->user_id ) )
					->size( Size::SIZE_20 )
					->render();
				?>
				<div class="tutor-text-small"><?php echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $last_reply->comment_date ) ) ) ); //phpcs:ignore ?></div>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="tutor-qna-card-actions">
		<button class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-sm-hidden">
			<?php esc_html_e( 'Reply', 'tutor' ); ?>
		</button>
		<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-flex">
			<button 
				x-ref="trigger" 
				@click="toggle()" 
				class="tutor-btn tutor-btn-text tutor-btn-x-small tutor-btn-icon tutor-qna-card-actions-more">
				<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES ); ?>
			</button>

			<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
				<div class="tutor-popover-menu">
					<button class="tutor-popover-menu-item">
						<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
						<?php esc_html_e( 'Mark as Unread', 'tutor' ); ?>
					</button>
					<button class="tutor-popover-menu-item">
						<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
						<?php esc_html_e( 'Delete', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
