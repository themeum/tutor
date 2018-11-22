
<div class="dozent-quiz-settings-wrap">

	<div class="dozent-option-field-row">
		<div class="dozent-option-field-label">
			<label for=""><?php _e('Time Limit', 'dozent'); ?></label>
		</div>
		<div class="dozent-option-field">
			<div class="dozent-option-gorup-fields-wrap">
				<div class="dozent-option-group-field">
					<input type="text" name="quiz_option[time_limit][time_value]" value="<?php echo dozent_utils()->get_quiz_option(get_the_ID(), 'time_limit.time_value', 0) ?>">
				</div>

				<div class="dozent-option-group-field">

					<?php $limit_time_type = dozent_utils()->get_quiz_option(get_the_ID(), 'time_limit.time_type', 'minutes') ?>

					<select name="quiz_option[time_limit][time_type]">
						<option value="seconds" <?php selected('seconds', $limit_time_type); ?> ><?php _e('Seconds', 'dozent'); ?></option>
						<option value="minutes" <?php selected('minutes', $limit_time_type); ?> ><?php _e('Minutes', 'dozent'); ?></option>
						<option value="hours" <?php selected('hours', $limit_time_type); ?>  ><?php _e('Hours', 'dozent'); ?></option>
						<option value="days" <?php selected('days', $limit_time_type); ?>  ><?php _e('Days', 'dozent'); ?></option>
						<option value="weeks" <?php selected('weeks', $limit_time_type); ?>  ><?php _e('Weeks', 'dozent'); ?></option>
					</select>
				</div>

			</div>

			<p class="desc"><?php _e('Time limit for this quiz. 0 means no time limit.', 'dozent'); ?></p>
		</div>
	</div>

    <div class="dozent-option-field-row">
        <div class="dozent-option-field-label">
            <label for=""><?php _e('Attempts Allowed', 'dozent'); ?></label>
        </div>
        <div class="dozent-option-field">
            <?php
            $default_attempts_allowed = dozent_utils()->get_option('quiz_attempts_allowed');
            $attempts_allowed = dozent_utils()->get_quiz_option(get_the_ID(), 'attempts_allowed', $default_attempts_allowed);
            ?>

            <div class="dozent-field-type-slider" data-min="0" data-max="20">
                <p class="dozent-field-type-slider-value"><?php echo $attempts_allowed; ?></p>
                <div class="dozent-field-slider"></div>
                <input type="hidden" value="<?php echo $attempts_allowed; ?>" name="quiz_option[attempts_allowed]" />
            </div>

            <p class="desc"><?php _e('Restriction on the number of attempts a student is allowed to take for this quiz. 0 for no limit', 'dozent'); ?></p>
        </div>
    </div>

    <!--
	<div class="dozent-option-field-row">
		<div class="dozent-option-field-label">
			<label for=""><?php /*_e('Minus Point', 'dozent'); */?></label>
		</div>
		<div class="dozent-option-field">
			<input type="number" name="quiz_option[minus_point]" value="<?php /*echo dozent_utils()->get_quiz_option(get_the_ID(), 'minus_point', 0) */?>">
			<p class="desc"><?php /*_e('Should apply minus point for wrong answer, 0 means not applicable', 'dozent'); */?></p>
		</div>
	</div>
-->

	<div class="dozent-option-field-row">
		<div class="dozent-option-field-label">
			<label for=""><?php _e('Passing Grade', 'dozent'); ?></label>
		</div>
		<div class="dozent-option-field">
			<div class="dozent-option-gorup-fields-wrap">
				<div class="dozent-option-group-field">
					<input type="number" name="quiz_option[passing_grade]" value="<?php echo dozent_utils()->get_quiz_option(get_the_ID(), 'passing_grade', 80) ?>" size="10">
				</div>
				<div class="dozent-option-group-field">
					%
				</div>
			</div>

			<p class="desc"><?php _e('Set the passing percentage for this quiz', 'dozent'); ?></p>
		</div>
	</div>



    <div class="dozent-option-field-row">
        <div class="dozent-option-field-label">
            <label for=""><?php _e('Max questions allowed to answer', 'dozent'); ?></label>
        </div>
        <div class="dozent-option-field">
            <input type="number" name="quiz_option[max_questions_for_answer]" value="<?php echo dozent_utils()->get_quiz_option(get_the_ID(), 'max_questions_for_answer', 10) ?>">
            <p class="desc"><?php _e('This amount of question will be available for students to answer, and question will comes randomly from all available questions belongs with a quiz, if this amount greater then available question, then all questions will be available for a student to answer.', 'dozent'); ?></p>
        </div>
    </div>


</div>