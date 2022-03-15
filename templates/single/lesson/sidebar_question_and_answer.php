<?php
/**
 * Question and answer in left sidebar at course spotlight
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.5.2
 */
global $post;
$currentPost = $post;

$course_id = tutor_utils()->get_course_id_by_content( $post );

$disable_qa_for_this_course = get_post_meta( $course_id, '_tutor_enable_qa', true ) != 'yes';
$enable_q_and_a_on_course   = tutor_utils()->get_option( 'enable_q_and_a_on_course' );
if ( ! $enable_q_and_a_on_course || $disable_qa_for_this_course ) {
	tutor_load_template( 'single.course.q_and_a_turned_off' );
	return;
}

echo '<div class="tutor-qna-sptolight-sidebar">';
	do_action( 'tutor_course/question_and_answer/before' );

$questions = tutor_utils()->get_qa_questions( 0, 20, $search_term = '', $question_id = null, $meta_query = null, $asker_id = null, $question_status = null, $count_only = false, $args = array('course_id' => $course_id) );
foreach ( $questions as $question ) {
	tutor_load_template_from_custom_path(
		tutor()->path . '/views/qna/qna-single.php',
		array(
			'question_id' => $question->comment_ID,
			'context'     => 'course-single-qna-sidebar',
		),
		false
	);
}

if(!count($questions)) : ?>
	<div class="tutor-empty-state-wrapper">
        <div class="tutor-empty-state td-empty-state tutor-p-32 tutor-text-center">
			<img src="<?php echo esc_url( tutor()->url . 'assets/images/empty-q&a.svg' ); ?>" alt="No Data Available in this Section" width="85%">
			<div class="tutor-text-regular-h6 tutor-color-text-subsued tutor-text-center tutor-mt-20">
				No questions yet
			</div>
			<div class="tutor-text-regular-caption tutor-color-text-hints tutor-mt-12">
				Describe what you’re trying to achieve and where you’re getting stuck
			</div>
		</div>
	</div>
<?php endif;

tutor_load_template_from_custom_path(
	tutor()->path . '/views/qna/qna-new.php',
	array(
		'course_id' => $course_id,
		'context'   => 'course-single-qna-sidebar',
	),
	false
);
do_action( 'tutor_course/question_and_answer/after' );
echo '</div>';

