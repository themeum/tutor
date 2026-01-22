<?php
/**
 * Lesson comment list template.
 *
 * @package Tutor\Templates
 * @subpackage Single\Lesson
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Sorting;
use TUTOR\Input;
use TUTOR\Lesson;

defined( 'ABSPATH' ) || exit;

$item_per_page = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page  = max( 1, Input::post( 'current_page', 0, Input::TYPE_INT ) );
$lesson_id     = Input::post( 'comment_post_ID', get_the_ID(), Input::TYPE_INT );

$comments_list_args = array(
	'post_id' => $lesson_id,
	'parent'  => 0,
	'paged'   => $current_page,
	'number'  => $item_per_page,
);

$comment_count_args = array(
	'post_id' => $lesson_id,
	'parent'  => 0,
	'count'   => true,
);

$comments_count = Lesson::get_comments( $comment_count_args );
$comment_list   = Lesson::get_comments( $comments_list_args );
?>
<?php if ( ! empty( $comment_list ) ) : ?>
<div x-ref="commentList">
	<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-t">
		<div class="tutor-small tutor-text-secondary">
			<?php esc_html_e( 'Comments', 'tutor' ); ?>
			<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( $comments_count ); ?>)</span>
		</div>
		<?php Sorting::make()->order( Input::get( 'order', 'DESC' ) )->render(); ?>
	</div>
	<?php tutor_load_template( 'learning-area.lesson.comment-items', compact( 'comment_list', 'lesson_id' ) ); ?>
</div>
<?php endif; ?>
