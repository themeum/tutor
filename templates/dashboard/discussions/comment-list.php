<?php
/**
 * Lesson comments list template.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 *
 * These variables are inherited from the parent template file.
 * template: /tutor/templates/dashboard/discussions.php
 *
 * @var string $discussion_url
 * @var int    $item_per_page
 * @var int    $offset
 * @var string $order_filter
 * @var int    $current_page
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\ConfirmationModal;
use Tutor\Components\EmptyState;
use Tutor\Components\Pagination;
use Tutor\Components\Sorting;
use TUTOR\Lesson;
use Tutor\Models\CourseModel;
use TUTOR\User;

$current_user_id    = get_current_user_id();
$is_only_instructor = User::is_only_instructor( $current_user_id );

$list_args = array(
	'post_type' => tutor()->lesson_post_type,
	'status'    => 'approve',
	'parent'    => 0,
	'number'    => $item_per_page,
	'offset'    => $offset,
	'orderby'   => 'comment_date',
	'order'     => $order_filter,
);

$count_args = array(
	'post_type' => tutor()->lesson_post_type,
	'status'    => 'approve',
	'parent'    => 0,
	'count'     => true,
);

if ( User::is_student_view() ) {
	$list_args['user_id']  = $current_user_id;
	$count_args['user_id'] = $current_user_id;
} elseif ( $is_only_instructor ) {
	$courses    = CourseModel::get_courses_by_instructor( $current_user_id );
	$course_ids = array_column( $courses, 'ID' );
	$lesson_ids = array();
	if ( count( $course_ids ) ) {
		$lesson_ids = tutor_utils()->get_course_content_ids_by( tutor()->lesson_post_type, tutor()->course_post_type, $course_ids );
	}

	$list_args['tutor_instructor_comments']  = true;
	$count_args['tutor_instructor_comments'] = true;

	$list_args['tutor_instructor_lesson_ids']  = $lesson_ids;
	$count_args['tutor_instructor_lesson_ids'] = $lesson_ids;

	$list_args['tutor_instructor_user_id']  = $current_user_id;
	$count_args['tutor_instructor_user_id'] = $current_user_id;
}

$tutor_comments_filter = function ( $pieces, $query ) {
	global $wpdb;
	if ( isset( $query->query_vars['tutor_instructor_comments'] ) && $query->query_vars['tutor_instructor_comments'] ) {
		$lesson_ids = $query->query_vars['tutor_instructor_lesson_ids'];
		$user_id    = $query->query_vars['tutor_instructor_user_id'];

		$in_lessons = '1=0';
		if ( is_array( $lesson_ids ) && count( $lesson_ids ) ) {
			$in_lessons = "{$wpdb->comments}.comment_post_ID IN (" . implode( ',', array_map( 'intval', $lesson_ids ) ) . ')';
		}

		//phpcs:ignore -- $in_lessons is safely constructed.
		$pieces['where'] .= $wpdb->prepare( " AND ( ($in_lessons) OR {$wpdb->comments}.user_id = %d )", $user_id );
	}
	return $pieces;
};

add_filter( 'comments_clauses', $tutor_comments_filter, 10, 2 );
$lesson_comments = Lesson::get_comments( $list_args );
$total_items     = Lesson::get_comments( $count_args );
remove_filter( 'comments_clauses', $tutor_comments_filter, 10 );
?>

<div class="tutor-flex tutor-items-center tutor-justify-between tutor-px-6 tutor-py-5 tutor-border-b">
	<div class="tutor-small tutor-text-secondary">
		<?php esc_html_e( 'Comments', 'tutor' ); ?>
		<span class="tutor-text-primary tutor-font-medium">(<?php echo esc_html( $total_items ); ?>)</span>
	</div>
	<div class="tutor-discussion-filter-right">
		<?php Sorting::make()->order( $order_filter )->render(); ?>
	</div>
</div>

<?php if ( empty( $lesson_comments ) ) : ?>
	<?php
		EmptyState::make()
			->title( 'No Comments Found!' )
			->icon( tutor_utils()->get_themed_svg( 'images/illustrations/comments-empty.svg' ) )
			->render();
	?>
<?php else : ?>
<div class="tutor-discussion-card-wrapper tutor-flex tutor-flex-column tutor-gap-4 tutor-sm-gap-none tutor-p-6 tutor-sm-p-none">
	<?php
	foreach ( $lesson_comments as $lesson_comment ) :
		tutor_load_template(
			'dashboard.discussions.comment-card',
			array(
				'lesson_comment' => $lesson_comment,
				'discussion_url' => $discussion_url,
			)
		);
	endforeach;
	?>
</div>
<?php endif; ?>

<?php
Pagination::make()
	->current( $current_page )
	->total( $total_items )
	->limit( $item_per_page )
	->attr( 'class', 'tutor-px-6 tutor-pb-6 tutor-sm-p-5 tutor-sm-border-t' )
	->render();
?>

<?php
if ( ! empty( $lesson_comments ) ) {
	ConfirmationModal::make()
		->id( 'tutor-comment-delete-modal' )
		->title( __( 'Delete This Comment?', 'tutor' ) )
		->message( __( 'Are you sure you want to delete this comment permanently? Please confirm your choice.', 'tutor' ) )
		->confirm_text( __( 'Yes, Delete This', 'tutor' ) )
		->confirm_handler( 'deleteCommentMutation?.mutate({ comment_id: payload?.commentId })' )
		->mutation_state( 'deleteCommentMutation' )
		->render();
}
