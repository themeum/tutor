<?php
/**
 * Quiz list single view
 *
 * @package Tutor\Views
 * @subpackage Tutor\Fragments
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

$data       = isset( $data ) && is_array( $data ) ? $data : array();
$quiz_id    = isset( $data['quiz_id'] ) ? $data['quiz_id'] : 0;
$topic_id   = isset( $data['topic_id'] ) ? $data['topic_id'] : 0;
$quiz_title = isset( $data['quiz_title'] ) ? $data['quiz_title'] : '';
?>
<div data-course_content_id="<?php echo esc_attr( $quiz_id ); ?>" id="tutor-quiz-<?php echo esc_attr( $quiz_id ); ?>" class="course-content-item tutor-quiz tutor-quiz-<?php echo esc_attr( $quiz_id ); ?>">
	<div class="tutor-course-content-top tutor-d-flex tutor-align-center">
		<span class="tutor-color-muted tutor-icon-hamburger-menu tutor-cursor-move tutor-px-12"></span>
		<a href="javascript:;" class="<?php echo $topic_id > 0 ? 'open-tutor-quiz-modal' : ''; ?>" data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>" data-topic-id="<?php echo esc_attr( $topic_id ); ?>"> 
			<?php echo esc_html( stripslashes( $quiz_title ) ); ?> 
		</a>
		<div class="tutor-course-content-top-right-action">
			<?php do_action( 'tutor_course_builder_before_quiz_btn_action', $quiz_id ); ?>
			<?php if ( $topic_id > 0 ) : ?>
				<a href="javascript:;" class="open-tutor-quiz-modal tutor-iconic-btn" data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>" data-topic-id="<?php echo esc_attr( $topic_id ); ?>"> 
					<span class="tutor-icon-edit" aria-hidden="true"></span>
				</a>
			<?php endif; ?>
			<a href="javascript:;" class="tutor-delete-quiz-btn tutor-iconic-btn" data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>">
				<span class="tutor-icon-trash-can-line" aria-hidden="true"></span>
			</a>
		</div>
	</div>
</div>
