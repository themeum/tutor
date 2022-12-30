<?php
/**
 * Tutor Q&A
 *
 * @package Tutor\Views
 * @subpackage Tutor\Q&A
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

extract( $data ); // $course_id, $context.
?>
<div class="tutor-qa-new tutor-quesanswer" data-course_id="<?php echo esc_attr( $course_id ); ?>" data-question_id="0" data-context="<?php echo esc_attr( $context ); ?>">
	<div class="tutor-quesanswer-askquestion tutor-qna-reply-editor">

		<?php
			$placeholder = __( 'Do you have any questions?', 'tutor' );
			$text_editor = '<textarea placeholder="' . $placeholder . '" class="tutor-form-control"></textarea>';
            //phpcs:ignore
			echo apply_filters(
				'tutor_qna_text_editor',
				$text_editor
			);
			?>
		<?php if ( 'course-single-qna-sidebar' == $data['context'] ) : ?>
			<div class="sidebar-ask-new-qna-submit tutor-row tutor-mt-16">
				<div class="tutor-col">
					<button class="sidebar-ask-new-qna-cancel-btn tutor-btn tutor-btn-outline-primary tutor-btn-block">
						<?php esc_html_e( 'Cancel', 'tutor' ); ?>
					</button>
				</div>

				<div class="tutor-col">
					<button class="sidebar-ask-new-qna-submit-btn tutor-btn tutor-btn-primary tutor-btn-block">
						<?php esc_html_e( 'Submit', 'tutor' ); ?>
					</button>
				</div>
			</div>

			<div class="sidebar-ask-new-qna-btn-wrap">
				<a class="sidebar-ask-new-qna-btn tutor-btn tutor-btn-primary tutor-btn-block">
					<?php esc_html_e( 'Ask a New Question', 'tutor' ); ?>
				</a>
			</div>
		<?php else : ?>
			<div class="tutor-d-flex tutor-justify-end tutor-mt-24">
				<button class="sidebar-ask-new-qna-submit-btn tutor-btn tutor-btn-primary">
					<?php esc_html_e( 'Ask Question', 'tutor' ); ?>
				</button>
			</div>
		<?php endif ?>
	</div>
</div>
<div class="tutor-qna-single-question"></div>
