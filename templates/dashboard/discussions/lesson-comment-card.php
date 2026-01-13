<?php
/**
 * Tutor dashboard Q&A card.
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
use TUTOR\Lesson;

// Comment read-unread feature does not exist currently, will be added in future.
$is_unread  = 0;
$course     = get_post( tutor_utils()->get_course_id_by( 'lesson', $lesson_comment->comment_post_ID ) );
$last_reply = null;
$replies    = Lesson::get_comments(
	array(
		'parent' => $lesson_comment->comment_ID,
		'order'  => 'DESC',
	)
);

if ( ! empty( $replies ) ) {
	$last_reply = $replies[0];
}

$single_url = UrlHelper::add_query_params(
	$discussion_url,
	array(
		'tab' => 'lesson-comments',
		'id'  => $lesson_comment->comment_ID,
	)
);
?>
<div class="tutor-qna-card <?php echo esc_attr( $is_unread ? 'unread' : '' ); ?>">
	<?php
		Avatar::make()
			->src( tutor_utils()->get_user_avatar_url( $lesson_comment->user_id ) )
			->size( Size::SIZE_32 )
			->render();
	?>
	<div class="tutor-qna-card-content">
		<div class="tutor-qna-card-top">
			<div class="tutor-qna-card-author"><?php echo esc_html( $lesson_comment->comment_author ); ?></div>
			<div>
				<span class="tutor-text-subdued"><?php esc_html_e( 'comment on', 'tutor' ); ?></span> 
				<?php PreviewTrigger::make()->id( $lesson_comment->comment_post_ID )->render(); ?>
				<span class="tutor-text-subdued"><?php esc_html_e( 'in', 'tutor' ); ?></span> 
				<?php PreviewTrigger::make()->id( $course->ID )->render(); ?>
			</div>
		</div>
		<h6 class="tutor-qna-card-title"><?php echo wp_kses_post( $lesson_comment->comment_content ); ?></h6>
		<div class="tutor-qna-card-meta">
			<button class="tutor-qna-card-meta-reply-button">
				<?php esc_html_e( 'Reply', 'tutor' ); ?>
			</button>
			<div class="tutor-flex tutor-items-center tutor-gap-2">
				<?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 20, 20 ); ?>
				<?php echo esc_html( count( $replies ) ); ?>
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
				<div class="tutor-text-small"><?php echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $lesson_comment->comment_date_gmt ) ) ) ); //phpcs:ignore ?></div>
			<?php } ?>
		</div>
	</div>
	<div class="tutor-qna-card-actions">
		<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-sm-hidden">
			<?php esc_html_e( 'Reply', 'tutor' ); ?>
		</a>
		<button class="tutor-btn tutor-btn-text tutor-btn-x-small tutor-btn-icon"
			@click="TutorCore.modal.showModal('tutor-comment-delete-modal', { commentId: <?php echo esc_html( $lesson_comment->comment_ID ); ?> });">
			<?php tutor_utils()->render_svg_icon( Icon::DELETE_2 ); ?>
		</button>
	</div>
</div>
