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

?>
<div data-course_content_id="<?php echo esc_attr( $data['quiz_id'] ); ?>" id="tutor-quiz-<?php echo esc_attr( $data['quiz_id'] ); ?>" class="course-content-item tutor-quiz tutor-quiz-<?php echo esc_attr( $data['quiz_id'] ); ?>">
	<div class="tutor-course-content-top tutor-d-flex tutor-align-center">
		<span class="tutor-color-muted tutor-icon-hamburger-menu tutor-cursor-move tutor-px-12"></span>
		<a href="javascript:;" class="<?php echo $data['topic_id'] > 0 ? 'open-tutor-quiz-modal' : ''; ?>" data-quiz-id="<?php echo esc_attr( $data['quiz_id'] ); ?>" data-topic-id="<?php echo esc_attr( $data['topic_id'] ); ?>"> 
			<?php echo esc_html( stripslashes( $data['quiz_title'] ) ); ?> 
		</a>
		<div class="tutor-course-content-top-right-action">
			<?php do_action( 'tutor_course_builder_before_quiz_btn_action', $data['quiz_id'] ); ?>
			<?php if ( $data['topic_id'] > 0 ) : ?>
				<a href="javascript:;" class="open-tutor-quiz-modal tutor-iconic-btn" data-quiz-id="<?php echo esc_attr( $data['quiz_id'] ); ?>" data-topic-id="<?php echo esc_attr( $data['topic_id'] ); ?>"> 
					<span class="tutor-icon-edit" area-hidden="true"></span>
				</a>
			<?php endif; ?>
			<a href="javascript:;" class="tutor-delete-quiz-btn tutor-iconic-btn" data-quiz-id="<?php echo esc_attr( $data['quiz_id'] ); ?>">
				<span class="tutor-icon-trash-can-line" area-hidden="true"></span>
			</a>
		</div>
	</div>
</div>
