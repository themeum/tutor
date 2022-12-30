<?php
/**
 * Quiz Modal Form
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>
<input type="hidden" name="quiz_id" value="<?php echo esc_attr( $quiz_id ); ?>"/>
<div id="quiz-builder-tab-quiz-info">
	<div class="tutor-mb-32">
		<label class="tutor-form-label"><?php esc_html_e( 'Quiz Title', 'tutor' ); ?></label>
		<div class="tutor-mb-16">
			<input type="text" name="quiz_title" class="tutor-form-control tutor-mb-12" placeholder="<?php esc_attr_e( 'Type your quiz title here', 'tutor' ); ?>" value="<?php echo esc_html( $quiz ? htmlspecialchars( stripslashes( $quiz->post_title ) ) : '' ); ?>"/>
		</div>
	</div>
	<div>
		<label class="tutor-form-label"><?php esc_html_e( 'Summary', 'tutor' ); ?></label>
		<div class="tutor-mb-16">
			<textarea name="quiz_description" class="tutor-form-control tutor-mb-12" rows="5"><?php echo esc_textarea( stripslashes( $quiz ? $quiz->post_content : '' ) ); ?></textarea>
		</div>
	</div>
	<?php do_action( 'tutor_quiz_edit_modal_info_tab_after', $quiz ); ?>
</div>

<div id="quiz-builder-tab-questions" class="quiz-builder-tab-container tutor-mb-32">
	<div class="quiz-builder-questions-wrap">
		<?php
		$questions = ( $quiz_id && $quiz_id > 0 ) ? tutor_utils()->get_questions_by_quiz( $quiz_id ) : array();

		if ( $questions ) {
			foreach ( $questions as $question ) {
				$id_target = 'quiz-popup-menu-' . $question->question_id;
				?>
				<div class="tutor-quiz-item quiz-builder-question-wrap" data-question-id="<?php echo esc_attr( $question->question_id ); ?>">
					<div class="tutor-quiz-item-label">
						<span class="tutor-quiz-item-draggable tutor-icon-drag question-sorting"></span>
						<h6 class="tutor-quiz-item-name">
							<?php echo esc_html( $question->question_title ); ?>
						</h6>
					</div>
					<div class="tutor-quiz-item-action tutor-align-center">
						<div class="tutor-quiz-item-type">
							<?php
								$type = tutor_utils()->get_question_types( $question->question_type );
								echo wp_kses( stripslashes( $type['icon'] ), tutor_utils()->allowed_icon_tags() ) . ' ' . esc_html( $type['name'] );
							?>
						</div>
						<div class="tutor-dropdown-parent">
							<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
								<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
							</button>
							<ul class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
								<li>
									<a href="#" class="tutor-dropdown-item tutor-quiz-open-question-form" data-question-id="<?php echo esc_attr( $question->question_id ); ?>">
										<span class="tutor-icon-edit tutor-mr-8" area-hidden="true"></span>
										<span><?php esc_html_e( 'Edit', 'tutor' ); ?></span>
									</a>
								</li>
								<li>
									<a href="#" class="tutor-dropdown-item tutor-quiz-question-trash" data-question-id="<?php echo esc_attr( $question->question_id ); ?>">
										<span class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></span>
										<span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<?php
			}
		}
		?>
	</div>

	<div>
		<a href="javascript:;" class="tutor-quiz-open-question-form tutor-btn tutor-btn-outline-primary tutor-btn-md">
			<i class="tutor-icon-plus-square tutor-mr-8" area-hidden="true"></i>
			<?php esc_html_e( 'Add Question', 'tutor' ); ?>
		</a>
	</div>
</div>

<div id="quiz-builder-tab-settings" class="quiz-builder-tab-container">
	<div class="tutor-mb-32">
		<label class="tutor-form-label">
			<?php esc_html_e( 'Time Limit', 'tutor' ); ?>
		</label>
		<div class="tutor-row tutor-align-center">
			<div class="tutor-col-3">
				<input type="number" class="tutor-form-control" min="0" name="quiz_option[time_limit][time_value]" value="<?php echo esc_attr( tutor_utils()->get_quiz_option( $quiz_id, 'time_limit.time_value', 0 ) ); ?>">
			</div>
			<div class="tutor-col-3">
				<?php $limit_time_type = tutor_utils()->get_quiz_option( $quiz_id, 'time_limit.time_type', 'minutes' ); ?>
				<select name="quiz_option[time_limit][time_type]" class="tutor-form-control">
					<option value="seconds" <?php selected( 'seconds', $limit_time_type ); ?>><?php esc_html_e( 'Seconds', 'tutor' ); ?></option>
					<option value="minutes" <?php selected( 'minutes', $limit_time_type ); ?>><?php esc_html_e( 'Minutes', 'tutor' ); ?></option>
					<option value="hours" <?php selected( 'hours', $limit_time_type ); ?>><?php esc_html_e( 'Hours', 'tutor' ); ?></option>
					<option value="days" <?php selected( 'days', $limit_time_type ); ?>><?php esc_html_e( 'Days', 'tutor' ); ?></option>
					<option value="weeks" <?php selected( 'weeks', $limit_time_type ); ?>><?php esc_html_e( 'Weeks', 'tutor' ); ?></option>
				</select>
			</div>
			<div class="tutor-col-6">
				<label class="tutor-form-toggle">
					<input type="checkbox" class="tutor-form-toggle-input" value="1" name="quiz_option[hide_quiz_time_display]" <?php checked( '1', tutor_utils()->get_quiz_option( $quiz_id, 'hide_quiz_time_display' ) ); ?> />
					<span class="tutor-form-toggle-control"></span> <?php esc_html_e( 'Hide quiz time - display', 'tutor' ); ?>
				</label>
			</div>
		</div>
		<div class="tutor-form-feedback">
			<?php esc_html_e( 'Time limit for this quiz. 0 means no time limit.', 'tutor' ); ?>
		</div>
	</div>

	<div class="tutor-mb-32">
		<label class="tutor-form-label">
			<?php esc_html_e( 'Quiz Feedback Mode', 'tutor' ); ?>
		</label>
		<div>
			<div class="tutor-fs-7 tutor-color-muted tutor-mb-12">
				(<?php esc_html_e( 'Pick the quiz system"s behaviour on choice based questions.', 'tutor' ); ?>)
			</div>

			<label class="tutor-radio-select tutor-bg-white tutor-mb-8">
				<input class="tutor-form-check-input" type="radio" name="quiz_option[feedback_mode]" value="default" <?php checked( 'default', tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode', 'default' ) ); ?>>
				<div class="tutor-radio-select-content">
					<span class="tutor-radio-select-title"><?php esc_html_e( 'Default', 'tutor' ); ?></span>
					<?php esc_html_e( 'Answers shown after quiz is finished', 'tutor' ); ?>
				</div>
			</label>

			<label class="tutor-radio-select tutor-bg-transparent tutor-my-8">
				<input class="tutor-form-check-input" type="radio" name="quiz_option[feedback_mode]" value="reveal" <?php checked( 'reveal', tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode' ) ); ?>>
				<div class="tutor-radio-select-content">
					<span class="tutor-radio-select-title"><?php esc_html_e( 'Reveal Mode', 'tutor' ); ?></span>
					<?php esc_html_e( 'Show result after the attempt.', 'tutor' ); ?>
				</div>
			</label>

			<label class="tutor-radio-select tutor-bg-transparent tutor-my-8">
				<input class="tutor-form-check-input" type="radio" name="quiz_option[feedback_mode]" value="retry" <?php checked( 'retry', tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode' ) ); ?>>
				<div class="tutor-radio-select-content">
					<span class="tutor-radio-select-title"><?php esc_html_e( 'Retry Mode', 'tutor' ); ?></span>
					<?php esc_html_e( 'Reattempt quiz any number of times. Define Attempts Allowed below.', 'tutor' ); ?>
				</div>
			</label>
		</div>
	</div>

	<div class="tutor-mb-32 tutor-quiz-slider tutor-attempt-allowed-slider" style="<?php echo esc_attr( tutor_utils()->get_quiz_option( $quiz_id, 'feedback_mode', 'default' ) === 'retry' ? 'display: block' : 'display: none' ); ?>">
		<label class="tutor-form-label">
			<?php esc_html_e( 'Attempts Allowed', 'tutor' ); ?>
		</label>
		<?php
			$default_attempts_allowed = tutor_utils()->get_option( 'quiz_attempts_allowed' );
			$attempts_allowed         = (int) tutor_utils()->get_quiz_option( $quiz_id, 'attempts_allowed', $default_attempts_allowed );
		?>
		<div class="tutor-field-type-slider tutor-p-0" data-min="0" data-max="20">
			<p class="tutor-field-type-slider-value"><?php echo esc_html( $attempts_allowed ); ?></p>
			<div class="tutor-field-slider"></div>
			<input type="hidden" value="<?php echo esc_attr( $attempts_allowed ); ?>" name="quiz_option[attempts_allowed]" />
		</div>
		<div class="tutor-form-feedback">
			<?php esc_html_e( 'Restriction on the number of attempts a student is allowed to take for this quiz. 0 for no limit', 'tutor' ); ?>
		</div>
	</div>

	<?php do_action( 'tutor_quiz_builder_settings_tab_passing_grade_before', $course_id, $quiz_id ); ?>

	<div class="tutor-mb-32">
		<label class="tutor-form-label">
			<?php esc_html_e( 'Passing Grade (%)', 'tutor' ); ?>
		</label>
		<input type="number" class="tutor-form-control" name="quiz_option[passing_grade]" value="<?php echo esc_attr( tutor_utils()->get_quiz_option( $quiz_id, 'passing_grade', 80 ) ); ?>" size="10" min="0"/>
		<div class="tutor-form-feedback">
			<?php esc_html_e( 'Set the passing percentage for this quiz', 'tutor' ); ?>
		</div>
	</div>

	<div class="tutor-mb-32">
		<label class="tutor-form-label">
			<?php esc_html_e( 'Max Question Allowed to Answer', 'tutor' ); ?>
		</label>
		<input type="number" class="tutor-form-control" name="quiz_option[max_questions_for_answer]" value="<?php echo esc_attr( tutor_utils()->get_quiz_option( $quiz_id, 'max_questions_for_answer', 10 ) ); ?>" min="1"/>
		<div class="tutor-form-feedback">
			<?php esc_html_e( 'This amount of question will be available for students to answer, and question will comes randomly from all available questions belongs with a quiz, if this amount is greater than available question, then all questions will be available for a student to answer.', 'tutor' ); ?>
		</div>
	</div>

	<?php do_action( 'tutor_quiz_edit_modal_settings_tab_after_max_allowed_questions', $quiz ); ?>

	<div class="tutor-quiz-advance-settings tutor-bg-white tutor-cursor-pointer tutor-mb-32">
		<!-- Header -->
		<div class="tutor-row tutor-align-center tutor-quiz-advance-header tutor-g-0">
			<div class="tutor-col">
				<div class="tutor-row tutor-align-center">
					<div class="tutor-col-auto">
						<span><i class="tutor-icon-gear"></i></span>
					</div>
					<div class="tutor-col tutor-p-0 tutor-fs-6 tutor-fw-medium tutor-color-secondary">
						<?php esc_html_e( 'Advance Settings', 'tutor' ); ?>
					</div>
				</div>
			</div>
			<div class="tutor-col-auto">
				<i class="tutor-icon-angle-down"></i>
			</div>
		</div>

		<!-- Fields -->
		<div class="tutor-quiz-advance-content tutor-p-32">
			<div class="tutor-quiz-advance-settings-fields tutor-row">
				<div class="tutor-col-12 tutor-mb-32">
					<label class="tutor-form-toggle">
						<input type="checkbox" class="tutor-form-toggle-input" value="1" name="quiz_option[quiz_auto_start]" <?php checked( '1', tutor_utils()->get_quiz_option( $quiz_id, 'quiz_auto_start' ) ); ?> />
						<span class="tutor-form-toggle-control"></span> <?php esc_html_e( 'Quiz Auto Start', 'tutor' ); ?>
					</label>
					<div class="tutor-form-feedback">
						<?php esc_html_e( 'If you enable this option, the quiz will start automatically after the page is loaded.', 'tutor' ); ?>
					</div>
				</div>

				<div class="tutor-col-12 tutor-col-sm-6 tutor-mb-32">
					<label class="tutor-form-label">
						<?php esc_html_e( 'Question Layout', 'tutor' ); ?>
					</label>
					<select class="tutor-form-control" name="quiz_option[question_layout_view]">
						<option value=""><?php esc_html_e( 'Set question layout view', 'tutor' ); ?></option>
						<option value="single_question" <?php selected( 'single_question', tutor_utils()->get_quiz_option( $quiz_id, 'question_layout_view' ) ); ?>> <?php esc_html_e( 'Single Question', 'tutor' ); ?> </option>
						<option value="question_pagination" <?php selected( 'question_pagination', tutor_utils()->get_quiz_option( $quiz_id, 'question_layout_view' ) ); ?>> <?php esc_html_e( 'Question Pagination', 'tutor' ); ?> </option>
						<option value="question_below_each_other" <?php selected( 'question_below_each_other', tutor_utils()->get_quiz_option( $quiz_id, 'question_layout_view' ) ); ?>> <?php esc_html_e( 'Question below each other', 'tutor' ); ?> </option>
					</select>
				</div>

				<div class="tutor-col-12 tutor-col-sm-6 tutor-mb-32">
					<label class="tutor-form-label">
						<?php esc_html_e( 'Questions Order', 'tutor' ); ?>
					</label>
					<select class="tutor-form-control" name="quiz_option[questions_order]">
						<option value="rand" <?php selected( 'rand', tutor_utils()->get_quiz_option( $quiz_id, 'questions_order' ) ); ?>> <?php esc_html_e( 'Random', 'tutor' ); ?> </option>
						<option value="sorting" <?php selected( 'sorting', tutor_utils()->get_quiz_option( $quiz_id, 'questions_order' ) ); ?>> <?php esc_html_e( 'Sorting', 'tutor' ); ?> </option>
						<option value="asc" <?php selected( 'asc', tutor_utils()->get_quiz_option( $quiz_id, 'questions_order' ) ); ?>> <?php esc_html_e( 'Ascending', 'tutor' ); ?> </option>
						<option value="desc" <?php selected( 'desc', tutor_utils()->get_quiz_option( $quiz_id, 'questions_order' ) ); ?>> <?php esc_html_e( 'Descending', 'tutor' ); ?> </option>
					</select>
				</div>

				<div class="tutor-col-12 tutor-mb-32">
					<label class="tutor-form-toggle">
						<input type="checkbox" class="tutor-form-toggle-input" value="1" name="quiz_option[hide_question_number_overview]" <?php checked( '1', tutor_utils()->get_quiz_option( $quiz_id, 'hide_question_number_overview' ) ); ?> />
						<span class="tutor-form-toggle-control"></span> <?php esc_html_e( 'Hide question number', 'tutor' ); ?></span>
					</label>
					<div class="tutor-form-feedback">
						<?php esc_html_e( 'Show/hide question number during attempt.', 'tutor' ); ?>
					</div>
				</div>

				<div class="tutor-col-12 tutor-mb-32">
					<label class="tutor-form-label">
						<?php esc_html_e( 'Short answer characters limit', 'tutor' ); ?>
					</label>
					<div class="tutor-row">
						<div class="tutor-col-auto">
							<input class="tutor-form-control" type="number" name="quiz_option[short_answer_characters_limit]" value="<?php echo esc_attr( tutor_utils()->get_quiz_option( $quiz_id, 'short_answer_characters_limit', 200 ) ); ?>" min="0">
						</div>
					</div>
					<div class="tutor-form-feedback">
						<?php esc_html_e( 'Student will place answer in short answer question type within this characters limit.', 'tutor' ); ?>
					</div>
				</div>

				<div class="tutor-col-12">
					<label class="tutor-form-label">
						<?php esc_html_e( 'Open-Ended/Essay questions answer character limit', 'tutor' ); ?>
					</label>
					<input style="max-width: 135px;" class="tutor-form-control" type="number" name="quiz_option[open_ended_answer_characters_limit]" value="<?php echo esc_attr( tutor_utils()->get_quiz_option( $quiz_id, 'open_ended_answer_characters_limit', 500 ) ); ?>" min="0">
					<div class="tutor-form-feedback">
						<?php esc_html_e( 'Students will place the answer in the Open-Ended/Essay question type within this character limit.', 'tutor' ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php do_action( 'tutor_quiz_edit_modal_settings_tab_after', $quiz ); ?>
</div>

