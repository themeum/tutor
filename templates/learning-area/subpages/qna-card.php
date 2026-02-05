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

global $tutor_course_id;

$current_user_id = get_current_user_id();
$is_user_asker   = $current_user_id === (int) $question->user_id;

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
	get_permalink( $tutor_course_id ),
	array(
		'subpage'     => 'qna',
		'question_id' => $question_id,
	)
);
?>
<div 
	class="tutor-discussion-card"
	x-show="editingId !== <?php echo (int) $question_id; ?>"
	x-data="tutorPopover({ placement: 'bottom-end' })"
	:class="{'active': open}"
>
	<?php Avatar::make()->user( $question->user_id )->size( Size::SIZE_32 )->render(); ?>
	<div class="tutor-discussion-card-content">
		<div class="tutor-discussion-card-top">
			<div class="tutor-discussion-card-author"><?php echo esc_html( $question->comment_author ); ?></div>
			<div class="tutor-text-tiny tutor-text-subdued">
				<?php
					/* translators: %s: time difference */
					echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $question->comment_date_gmt ) ) ) );
				?>
			</div>
		</div>
		<h6 class="tutor-discussion-card-title" id="tutor-qna-text-<?php echo (int) $question_id; ?>"><?php echo wp_kses_post( $content ); ?></h6>
		<div class="tutor-flex tutor-items-center tutor-justify-between">
			<div class="tutor-discussion-card-meta">
				<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-discussion-card-meta-reply-button">
					<?php esc_html_e( 'Reply', 'tutor' ); ?>
				</a>
				<div class="tutor-flex tutor-items-center tutor-gap-2">
					<?php tutor_utils()->render_svg_icon( Icon::COMMENTS, 20, 20 ); ?> 
					<?php echo esc_html( $question->answer_count ); ?>
				</div>

				<?php if ( $last_reply ) : ?>
				<div class="tutor-flex tutor-items-center tutor-gap-3 tutor-sm-ml-2">
					<?php Avatar::make()->user( $last_reply->user_id )->size( Size::SIZE_20 )->render(); ?>
					<div class="tutor-text-small">
						<?php
							/* translators: %s: time difference */
							echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $last_reply->comment_date_gmt ) ) ) );
						?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="tutor-discussion-card-actions">
		<a href="<?php echo esc_url( $single_url ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-x-small tutor-sm-hidden">
			<?php esc_html_e( 'Reply', 'tutor' ); ?>
		</a>
		<?php if ( $is_user_asker ) : ?>
		<div class="tutor-flex">
			<button 
				x-ref="trigger" 
				@click="toggle()" 
				class="tutor-btn tutor-btn-secondary tutor-btn-x-small tutor-btn-icon tutor-discussion-card-actions-trigger">
				<?php tutor_utils()->render_svg_icon( Icon::ELLIPSES ); ?>
			</button>
			<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()" class="tutor-popover">
				<div class="tutor-popover-menu" style="min-width: 110px;">
					<button class="tutor-popover-menu-item tutor-gap-5" @click="setEditing(<?php echo (int) $question_id; ?>); hide()">
						<?php tutor_utils()->render_svg_icon( Icon::EDIT_2, 20, 20 ); ?>
						<?php esc_html_e( 'Edit', 'tutor' ); ?>
					</button>
					<button
						class="tutor-popover-menu-item tutor-gap-5 tutor-sm-border-t"
						@click="hide(); TutorCore.modal.showModal('tutor-qna-delete-modal', { questionId: <?php echo esc_html( $question_id ); ?> });"
					>
						<?php tutor_utils()->render_svg_icon( Icon::DELETE_2, 20, 20 ); ?>
						<?php esc_html_e( 'Delete', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>

<?php if ( $is_user_asker ) : ?>
<div x-show="editingId === <?php echo (int) $question_id; ?>" x-cloak class="tutor-card tutor-surface-l1-hover">
	<?php
	tutor_load_template(
		'dashboard.discussions.qna-form',
		array(
			'form_id'        => 'qna-edit-' . (int) $question_id,
			'default_value'  => $question->comment_content,
			'submit_handler' => '(data) => updateQnAMutation?.mutate({ ...data, question_id: ' . (int) $question->comment_ID . ' })',
			'cancel_handler' => 'setEditing(null)',
			'is_pending'     => 'updateQnAMutation?.isPending',
		)
	);
	?>
</div>
<?php endif; ?>
