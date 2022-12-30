<?php
/**
 * Template for single answer editor
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

if ( 'open_ended' === $question_type || 'short_answer' === $question_type ) {
	echo '<p class="open-ended-notice" style="color: #ff0000;">' .
			esc_html__( 'No option is necessary for this answer type', 'tutor' ) .
		'</p>';
	return '';
}

empty( $old_answer ) ? $old_answer = (object) array() : 0;
$answer_title                      = ! empty( $old_answer->answer_title ) ? stripslashes( $old_answer->answer_title ) : '';
$image_id                          = ! empty( $old_answer->image_id ) ? $old_answer->image_id : '';
$answer_view_format                = ! empty( $old_answer->answer_view_format ) ? $old_answer->answer_view_format : 'text';
$answer_two_gap_match              = ! empty( $old_answer->answer_two_gap_match ) ? stripslashes( $old_answer->answer_two_gap_match ) : '';
?>

<div class="tutor-quiz-question-answers-form">
	<input type="hidden" name="tutor_quiz_answer_id" value="<?php echo esc_attr( ! empty( $old_answer->answer_id ) ? $old_answer->answer_id : '' ); ?>" />

	<?php
	if ( 'true_false' === $question_type ) {
		?>
		<div class="tutor-quiz-builder-group">
			<!-- <h4><?php esc_html_e( 'Select the correct option', 'tutor' ); ?></h4> -->
			<div class="tutor-quiz-builder-row">
				<div class="tutor-quiz-builder-col auto-width">
					<label>
						<input type="radio" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][true_false]" value="true" checked="checked">
						<?php esc_html_e( 'True', 'tutor' ); ?>
					</label>
					<label>
						<input type="radio" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][true_false]" value="false">
						<?php esc_html_e( 'False', 'tutor' ); ?>
					</label>
				</div>
			</div>
		</div>
		<?php
	} elseif ( 'multiple_choice' === $question_type || 'single_choice' === $question_type || 'ordering' === $question_type ) {
		?>

		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Answer title', 'tutor' ); ?>
			</label>
			<div class="tutor-mb-16">
				<input class="tutor-form-control" type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_title]" value="<?php echo esc_attr( $answer_title ); ?>">
			</div>
		</div>

		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Upload Image', 'tutor' ); ?>
			</label>

			<?php
				// Load thumbnail segment.
				tutor_load_template_from_custom_path(
					tutor()->path . '/views/fragments/thumbnail-uploader.php',
					array(
						'media_id'   => $image_id,
						'input_name' => 'quiz_answer[' . $question_id . '][image_id]',
					),
					false
				);
			?>
		</div>

		<div class="tutor-row tutor-mb-32">
			<div class="tutor-col-12">
				<label class="tutor-form-label">
					<?php esc_html_e( 'Display format for options', 'tutor' ); ?>
				</label>
			</div>
			<div class="tutor-col-auto">
				<div class="tutor-form-check tutor-mb-16">
					<input type="radio" id="tutor_quiz_type_text" class="tutor-form-check-input" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_view_format]" value="text" <?php echo $answer_view_format ? checked( 'text', $answer_view_format ) : 'checked="checked"'; ?>/>
					<label for="tutor_quiz_type_text"><?php esc_html_e( 'Only text', 'tutor' ); ?></label>
				</div>
			</div>
			<div class="tutor-col-auto">
				<div class="tutor-form-check tutor-mb-16">
					<input type="radio" id="tutor_quiz_type_img" class="tutor-form-check-input" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_view_format]" value="image" <?php echo checked( 'image', $answer_view_format ); ?>/>
					<label for="tutor_quiz_type_img"><?php esc_html_e( 'Only Image', 'tutor' ); ?></label>
				</div>
			</div>
			<div class="tutor-col-auto">
				<div class="tutor-form-check tutor-mb-16">
					<input type="radio" id="tutor_quiz_type_img_text" class="tutor-form-check-input" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_view_format]" value="text_image" <?php echo checked( 'text_image', $answer_view_format ); ?>/>
					<label for="tutor_quiz_type_img_text"><?php esc_html_e( 'Text &amp; Image both', 'tutor' ); ?></label>
				</div>
			</div>
		</div>
		<?php
	} elseif ( 'fill_in_the_blank' === $question_type ) {
		?>

		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Question Title', 'tutor' ); ?>
			</label>
			<div class="tutor-mb-16">
				<input class="tutor-form-control" type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_title]" value="<?php echo esc_attr( $answer_title ); ?>">
				<div class="tutor-form-feedback">
					<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
					<div><?php echo wp_kses( __( 'Please make sure to use the <strong>{dash}</strong> variable in your question title to show the blanks in your question. You can use multiple <strong>{dash}</strong> variables in one question.', 'tutor' ), array( 'strong' => true ) ); ?></div>
				</div>
			</div>
		</div>

		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Correct Answer(s)', 'tutor' ); ?>
			</label>
			<div class="tutor-mb-16">
				<input class="tutor-form-control" type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_two_gap_match]" value="<?php echo esc_attr( $answer_two_gap_match ); ?>"/>
				<div class="tutor-form-feedback">
					<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
					<div><?php echo wp_kses( __( 'Separate multiple answers by a vertical bar <strong>|</strong>. 1 answer per <strong>{dash}</strong> variable is defined in the question. Example: Apple | Banana | Orange', 'tutor' ), array( 'strong' => true ) ); ?></div>
				</div>
			</div>
		</div>
		<?php
	} elseif ( 'answer_sorting' === $question_type ) {
		?>

		<div class="tutor-quiz-builder-group">
			<h4><?php esc_html_e( 'Answer title', 'tutor' ); ?></h4>
			<div class="tutor-quiz-builder-row">
				<div class="tutor-quiz-builder-col">
					<input type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_title]" value="">
				</div>
			</div>
		</div> <!-- /.tutor-quiz-builder-group -->

		<div class="tutor-quiz-builder-group">
			<h4><?php esc_html_e( 'Matched Answer title', 'tutor' ); ?></h4>
			<div class="tutor-quiz-builder-row">
				<div class="tutor-quiz-builder-col">
					<input type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][matched_answer_title]" value="">
				</div>
			</div>
			<p class="help"></p>
		</div> <!-- /.tutor-quiz-builder-group -->

		<?php
	} elseif ( 'matching' === $question_type ) {
		?>
		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Answer title', 'tutor' ); ?>
			</label>
			<div class="tutor-mb-16">
				<input class="tutor-form-control" type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_title]" value="<?php echo esc_attr( $answer_title ); ?>"/>
			</div>
		</div>

		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Matched Answer title', 'tutor' ); ?>
			</label>
			<div class="tutor-mb-16">
				<input class="tutor-form-control" type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][matched_answer_title]" value="<?php echo esc_attr( $answer_two_gap_match ); ?>"/>
			</div>
		</div>

		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Upload Image', 'tutor' ); ?>
			</label>

			<?php
				// Load thumbnail segment.
				tutor_load_template_from_custom_path(
					tutor()->path . '/views/fragments/thumbnail-uploader.php',
					array(
						'media_id'   => $image_id,
						'input_name' => 'quiz_answer[' . $question_id . '][image_id]',
					),
					false
				);
			?>
		</div> 

		<div class="tutor-row tutor-mb-32">
			<div class="tutor-col-12">
				<label class="tutor-form-label">
					<?php esc_html_e( 'Display format for options', 'tutor' ); ?>
				</label>
			</div>
			<div class="tutor-col-auto">
				<div class="tutor-form-check tutor-mb-16">
					<input type="radio" id="tutor_quiz_type_text" class="tutor-form-check-input" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_view_format]" value="text" <?php echo $answer_view_format ? checked( 'text', $answer_view_format ) : 'checked="checked"'; ?>/>
					<label for="tutor_quiz_type_text"><?php esc_html_e( 'Only text', 'tutor' ); ?></label>
				</div>
			</div>
			<div class="tutor-col-auto">
				<div class="tutor-form-check tutor-mb-16">
					<input type="radio" id="tutor_quiz_type_img" class="tutor-form-check-input" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_view_format]" value="image" <?php echo checked( 'image', $answer_view_format ); ?>/>
					<label for="tutor_quiz_type_img"><?php esc_html_e( 'Only Image', 'tutor' ); ?></label>
				</div>
			</div>
			<div class="tutor-col-auto">
				<div class="tutor-form-check tutor-mb-16">
					<input type="radio" id="tutor_quiz_type_img_text" class="tutor-form-check-input" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_view_format]" value="text_image" <?php echo checked( 'text_image', $answer_view_format ); ?>/>
					<label for="tutor_quiz_type_img_text"><?php esc_html_e( 'Text &amp; Image both', 'tutor' ); ?></label>
				</div>
			</div>
		</div>
		<?php
	} elseif ( 'image_matching' === $question_type ) {
		?>
		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Upload Image', 'tutor' ); ?>
			</label>

			<?php
				// Load thumbnail segment.
				tutor_load_template_from_custom_path(
					tutor()->path . '/views/fragments/thumbnail-uploader.php',
					array(
						'media_id'   => $image_id,
						'input_name' => 'quiz_answer[' . $question_id . '][image_id]',
					),
					false
				);
			?>
		</div> 
		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Image matched text', 'tutor' ); ?>
			</label>
			<div class="tutor-mb-16">
				<input class="tutor-form-control" type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_title]"  value="<?php echo esc_attr( $answer_title ); ?>"/>
			</div>
		</div>
		<?php
	} elseif ( 'image_answering' === $question_type ) {
		?>

		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Upload Image', 'tutor' ); ?>
			</label>

			<?php
				// Load thumbnail segment.
				tutor_load_template_from_custom_path(
					tutor()->path . '/views/fragments/thumbnail-uploader.php',
					array(
						'media_id'   => $image_id,
						'input_name' => 'quiz_answer[' . $question_id . '][image_id]',
					),
					false
				);
			?>
		</div> 

		<div class="tutor-mb-32">
			<label class="tutor-form-label">
				<?php esc_html_e( 'Answer input value', 'tutor' ); ?>
			</label>
			<div class="tutor-mb-16">
				<input class="tutor-form-control" type="text" name="quiz_answer[<?php echo esc_attr( $question_id ); ?>][answer_title]" value="<?php echo esc_attr( $answer_title ); ?>"/>
				<div class="tutor-form-feedback">
					<i class="tutor-icon-circle-info-o tutor-form-feedback-icon"></i>
					<div><?php echo wp_kses( __( 'The answers that students enter should match with this text. Write in <strong>small caps</strong>', 'tutor' ), array( 'strong' => true ) ); ?></div>
				</div>
			</div>
		</div>
		<?php
	}
	?>

	<div class="tutor-quiz-answers-form-footer">
		<button type="button" id="quiz-answer-save-btn" class="tutor-answer-save-btn tutor-btn tutor-btn-primary tutor-btn-sm">
			<?php esc_html_e( 'Update Answer', 'tutor' ); ?>
		</button>
	</div>
</div>
