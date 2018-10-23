
<div class="tutor-quiz-settings-wrap">



	<div class="tutor-option-field-row">
		<div class="tutor-option-field-label">
			<label for=""><?php _e('Time Limit', 'tutor'); ?></label>
		</div>
		<div class="tutor-option-field">
			<div class="tutor-option-gorup-fields-wrap">
				<div class="tutor-option-group-field">
					<input type="text" name="quiz_option[time_limit][time_value]" value="<?php echo tutor_utils()->get_quiz_option(get_the_ID(), 'time_limit.time_value', 0) ?>">
				</div>

				<div class="tutor-option-group-field">

					<?php $limit_time_type = tutor_utils()->get_quiz_option(get_the_ID(), 'time_limit.time_type', 'minutes') ?>

					<select name="quiz_option[time_limit][time_type]">
						<option value="seconds" <?php selected('seconds', $limit_time_type); ?> ><?php _e('Seconds', 'tutor'); ?></option>
						<option value="minutes" <?php selected('minutes', $limit_time_type); ?> ><?php _e('Minutes', 'tutor'); ?></option>
						<option value="hours" <?php selected('hours', $limit_time_type); ?>  ><?php _e('Hours', 'tutor'); ?></option>
						<option value="days" <?php selected('days', $limit_time_type); ?>  ><?php _e('Days', 'tutor'); ?></option>
						<option value="weeks" <?php selected('weeks', $limit_time_type); ?>  ><?php _e('Weeks', 'tutor'); ?></option>
					</select>
				</div>

			</div>

			<p class="desc"><?php _e('Time limit for quizzes in seconds. 0 mean no time limit.', 'tutor'); ?></p>
		</div>
	</div>

    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for=""><?php _e('Attempts Allowed', 'tutor'); ?></label>
        </div>
        <div class="tutor-option-field">
            <?php
            $default_attempts_allowed = tutor_utils()->get_option('quiz_attempts_allowed');
            $attempts_allowed = tutor_utils()->get_quiz_option(get_the_ID(), 'attempts_allowed', $default_attempts_allowed);
            ?>

            <div class="tutor-field-type-slider" data-min="0" data-max="20">
                <p class="tutor-field-type-slider-value"><?php echo $attempts_allowed; ?></p>
                <div class="tutor-field-slider"></div>
                <input type="hidden" value="<?php echo $attempts_allowed; ?>" name="quiz_option[attempts_allowed]" />
            </div>

            <p class="desc"><?php _e('Restriction on the number of attempts students are allowed at the quiz. 0 for no limit', 'tutor'); ?></p>
        </div>
    </div>

	<div class="tutor-option-field-row">
		<div class="tutor-option-field-label">
			<label for=""><?php _e('Minus Point', 'tutor'); ?></label>
		</div>
		<div class="tutor-option-field">
			<input type="number" name="quiz_option[minus_point]" value="<?php echo tutor_utils()->get_quiz_option(get_the_ID(), 'minus_point', 0) ?>">
			<p class="desc"><?php _e('Should apply minus point for wrong answer, 0 means not applicable', 'tutor'); ?></p>
		</div>
	</div>


	<div class="tutor-option-field-row">
		<div class="tutor-option-field-label">
			<label for=""><?php _e('Passing Grade', 'tutor'); ?></label>
		</div>
		<div class="tutor-option-field">
			<div class="tutor-option-gorup-fields-wrap">
				<div class="tutor-option-group-field">
					<input type="number" name="quiz_option[passing_grade]" value="<?php echo tutor_utils()->get_quiz_option(get_the_ID(), 'passing_grade', 80) ?>" size="10">
				</div>
				<div class="tutor-option-group-field">
					%
				</div>
			</div>

			<p class="desc"><?php _e('Students must have to get equivalent this point to pass the quiz / Exam ', 'tutor'); ?></p>
		</div>
	</div>


</div>