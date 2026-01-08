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
use Tutor\Helpers\UrlHelper;

$current_user_id = get_current_user_id();
$context         = 'frontend-dashboard-qna-table-' . $view_as;
$key_slug        = 'frontend-dashboard-qna-table-student' === $context ? '_' . $current_user_id : '';
$meta            = $question->meta;
$is_read         = (int) tutor_utils()->array_get( 'tutor_qna_read' . $key_slug, $meta, 0 );
$is_unread       = 0 === $is_read;
$text_mark_as    = $is_unread ? __( 'Mark as Read', 'tutor' ) : __( 'Mark as Unread', 'tutor' );

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
	</div>
	<div class="tutor-qna-card-actions">
		<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-sm-hidden">
			<?php esc_html_e( 'Reply', 'tutor' ); ?>
		</a>
		<div x-data="tutorPopover({ placement: 'bottom-end' })" class="tutor-flex">
			<button 
				x-ref="trigger" 
				@click="toggle()" 
				class="tutor-btn tutor-btn-text tutor-btn-x-small tutor-btn-icon tutor-qna-card-actions-more">
				<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES ); ?>
			</button>

			<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
				<div class="tutor-popover-menu">
					<button 
						class="tutor-popover-menu-item"
						@click="handleReadUnreadQnA(<?php echo esc_html( $question_id ); ?>,'<?php echo esc_html( $context ); ?>')">
						<?php tutor_utils()->render_svg_icon( Icon::EDIT_2 ); ?>
						<?php echo esc_html( $text_mark_as ); ?>
					</button>
					<button
						class="tutor-popover-menu-item"
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
