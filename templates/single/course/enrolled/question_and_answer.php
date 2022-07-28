<?php
/**
 * Question and answer in single course details tab
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $post;
if ( NULL === $post && isset( $_POST['course_id'] ) ) {
    $post = get_post( $_POST['course_id'] );
}
$disable_qa_for_this_course = get_post_meta( $post->ID, '_tutor_enable_qa', true ) != 'yes';
$enable_q_and_a_on_course   = tutor_utils()->get_option( 'enable_q_and_a_on_course' );

if ( ! $enable_q_and_a_on_course || $disable_qa_for_this_course ) {
	tutor_load_template( 'single.course.q_and_a_turned_off' );
	return;
}
/**
 * Load more support added 
 *
 * @since v2.0.6
 */
$per_page       = tutor_utils()->get_option('pagination_per_page', 10);
$current_page   = max( 1, (int)tutor_utils()->avalue_dot( 'current_page', $_POST ) );
$offset         = (int) ( $per_page * $current_page ) - $per_page;
$is_load_more   = isset( $_POST['action'] ) && 'tutor_q_and_a_load_more' === $_POST['action'];
// Previous qna list.
$questions = tutor_utils()->get_qa_questions(
    $offset,
    $per_page,
    $search_term = '',
    $question_id = null, 
    $meta_query = null, 
    $asker_id = null,
    $question_status = null,
    $count_only = false,
    $args = array( 'course_id' => $post->ID )
);

$total_questions = tutor_utils()->get_qa_questions(
    0,
    0,
    $search_term = '',
    $question_id = null, 
    $meta_query = null, 
    $asker_id = null,
    $question_status = null,
    $count_only = true,
    $args = array( 'course_id' => $post->ID )
);
$load_more_btn  = '';
$max_page = (int) ceil( $total_questions / $per_page );
$data = array(
	'layout' => array(
		'type' 			 => 'load_more',
		'load_more_text' => __('Load More', 'tutor')
	),
	'ajax' => array(
		'action' 			=> 'tutor_q_and_a_load_more',
		'current_page_num' 	=> $current_page,
        'course_id'         => $post->ID,
    ),
);
$template = tutor()->path . 'templates/dashboard/elements/load-more.php';
if ( file_exists( $template ) && $max_page > $current_page  ) {
    ob_start();
	tutor_load_template_from_custom_path( $template, $data );
	$load_more_btn = apply_filters( 'tutor_q_and_a_load_more_button', ob_get_clean() );
}

// Add identifier when load more button should remove.
if ( $current_page >= $max_page ) {
	echo '<input type="hidden" id="tutor-hide-comment-load-more-btn">';
}

do_action('tutor_course/question_and_answer/before');
if ( $is_load_more ) {
    foreach ( $questions as $question ) {
        tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-single.php', array(
            'question_id'       => $question->comment_ID,
            'context'           => 'course-single-qna-single',
            'is_qna_load_more'  => true
        ), false);
    }
    return;
}
?>
<h3 class="tutor-fs-5 tutor-fw-bold tutor-color-black tutor-mb-20">
    <?php esc_html_e( 'Question & Answer', 'tutor' ); ?>
</h3>
<?php
// Load new question textarea.
tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-new.php', array(
    'course_id' => get_the_ID(),
    'context' => 'course-single-qna-single'
), false);
?>
<div class="tutor-pagination-wrapper-replaceable">
    <div class="tutor-pagination-content-appendable">
        <?php
            foreach ( $questions as $question ) {
                tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-single.php', array(
                    'question_id'       => $question->comment_ID,
                    'context'           => 'course-single-qna-single',
                    'is_qna_load_more'  => $is_load_more
                ), false);
            }
        ?>
    </div>
    <div class="tutor-button-wrapper tutor-mt-12 tutor-mb-24 tutor-d-flex tutor-justify-end">
        <?php
            echo $load_more_btn; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
    </div> 
</div>

<?php do_action('tutor_course/question_and_answer/after'); ?>
