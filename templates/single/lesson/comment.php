<?php
/**
 * Comments Template
 *
 * @package Tutor\Template
 */

use TUTOR\Lesson;

$per_page = 1;//tutor_utils()->get_option('pagination_per_page', 10);
$current_page = max(1, (int)tutor_utils()->avalue_dot('current_page', $_POST));
$lesson_id = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : get_the_ID();
$comments_list_args = array(
	'post_id' => $lesson_id,
	'parent' => 0,
	'paged' => $current_page,
	'number' => $per_page
);
$comment_count_args = array(
	'post_id' => $lesson_id,
	'parent' => 0,
	'count' => true
);
$comments 		= Lesson::get_comments( $comments_list_args );
$comments_count = Lesson::get_comments( $comment_count_args );
$action 		= $_POST['action'] ?? '';
$load_more_btn  = '';
$max_page = (int) ceil( $comments_count / $per_page );

// Prepare load more button.
$data = array(
	'layout' => array(
		'should_show'    => $max_page > $current_page ? true : false,
		'type' 			 => 'load_more',
		'load_more_text' => __('Load More', 'tutor')
	),
	'ajax' => array(
		'action' 			=> 'tutor_single_course_lesson_load_more',
		'lesson_id' 		=> $lesson_id,
		'current_page_num' 	=> $current_page
	)
);
$template = tutor()->path . 'templates/dashboard/elements/load-more.php';
if ( file_exists( $template ) ) {
	ob_start();
	tutor_load_template_from_custom_path( $template, $data );
	$load_more_btn = apply_filters( 'tutor_lesson_comment_load_more_button', ob_get_clean() );
}

if ( 'tutor_single_course_lesson_load_more' === $action ) {
	tutor_load_template(
		'single.lesson.comments-loop',
		array(
			'comments'  => $comments,
			'lesson_id' => $lesson_id,
		)
	);
	return;
}
?>

<div class="tutor-pagination-wrapper-replaceable tutor-single-course-lesson-comments tutor-pb-32" data-lesson_id="<?php echo $lesson_id; ?>">
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-36">
			<?php _e('Join the conversation', 'tutor'); ?>
		</div>
		<div class="tutor-conversation tutor-pb-20 tutor-pb-sm-48">
		<form class="tutor-comment-box" action="<?php echo get_home_url(); ?>/wp-comments-post.php" method="post">
			<input type="hidden" name="is_lesson_comment" value="true">
			<div class="comment-avatar">
				<img src="<?php echo get_avatar_url(get_current_user_id()); ?>" alt="">
			</div>
			<div class="tutor-comment-textarea">
				<textarea placeholder="<?php _e('Write your comment here…', 'tutor'); ?>" class="tutor-form-control" name="comment"></textarea>
				<input type="hidden" name="comment_post_ID" value="<?php echo $lesson_id; ?>" />
				<input type="hidden" name="comment_parent" value="0" />
			</div>
			<div class="tutor-comment-submit-btn">
				<button type="submit" class="tutor-btn tutor-btn-primary tutor-btn-sm tutor-lesson-comment">
					<?php _e('Submit', 'tutor'); ?>
				</button>
			</div>
		</form>
		<div class="tutor-pagination-content-appendable">
			<?php
				tutor_load_template(
					'single.lesson.comments-loop',
					array(
						'comments'  => $comments,
						'lesson_id' => $lesson_id,
					)
				);
			?>
		</div>
	</div>
	<?php echo $load_more_btn;?>
</div>