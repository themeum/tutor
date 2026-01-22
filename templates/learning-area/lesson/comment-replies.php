<?php
/**
 * Lesson comment replies template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Avatar;
use Tutor\Components\Constants\Size;
use TUTOR\Lesson;

defined( 'ABSPATH' ) || exit;

$replies     = Lesson::get_comments(
	array(
		'post_id' => $lesson_id,
		'parent'  => $comment_item->comment_ID,
	)
);
$reply_count = is_array( $replies ) ? count( $replies ) : 0;
?>
<?php if ( $reply_count > 0 ) : ?>
<div class="tutor-comment-replies" x-show="repliesExpanded" x-collapse>
	<?php foreach ( $replies as $reply_item ) : ?>
		<div class="tutor-flex tutor-gap-5">
			<?php Avatar::make()->user( $reply_item->user_id )->size( Size::SIZE_40 )->render(); ?>
			<div class="tutor-flex-1">
				<div class="tutor-flex tutor-items-center tutor-gap-5 tutor-mb-2 tutor-small">
					<span class="tutor-discussion-card-author">
						<?php echo esc_html( $reply_item->comment_author ); ?>
					</span> 
					<span class="tutor-text-subdued">
						<?php
							// Translators: %s is the time of comment.
							echo esc_html( sprintf( __( '%s ago', 'tutor' ), human_time_diff( strtotime( $reply_item->comment_date_gmt ) ) ) );
						?>
					</span>
				</div>
				<div class="tutor-p2 tutor-text-secondary">
					<?php echo wp_kses_post( $reply_item->comment_content ); ?>
				</div>
			</div>
		</div>
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
<?php endif; ?>
