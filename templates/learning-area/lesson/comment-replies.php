<?php
/**
 * Lesson Comment Replies Template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;
use TUTOR\Icon;
use TUTOR\Lesson;

defined( 'ABSPATH' ) || exit;

$replies     = $replies ?? Lesson::get_comments(
	array(
		'post_id' => $lesson_id,
		'parent'  => $comment_item->comment_ID,
		'order'   => 'ASC',
	)
);
$reply_count = is_array( $replies ) ? count( $replies ) : 0;
?>
<?php if ( $reply_count > 0 ) : ?>
<div id="tutor-comment-replies-<?php echo esc_attr( $comment_item->comment_ID ); ?>" class="tutor-replies-wrapper">
	<div class="tutor-comment-replies" x-show="repliesExpanded" x-collapse>
		<?php foreach ( $replies as $reply_item ) : ?>
			<?php
			tutor_load_template(
				'learning-area.lesson.comment-card',
				array(
					'comment_item' => $reply_item,
					'lesson_id'    => $lesson_id,
					'user_id'      => $user_id,
					'is_reply'     => true,
				)
			);
			?>
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
</div>
<?php endif; ?>
