<?php
global $post;
$currentPost = $post;

$course = tutor_utils()->get_course_by_quiz(get_the_ID());
$previous_attempts = tutor_utils()->quiz_attempts();
$attempted_count = is_array($previous_attempts) ? count($previous_attempts) : 0;

$attempts_allowed = tutor_utils()->get_quiz_option(get_the_ID(), 'attempts_allowed', 0);
$passing_grade = tutor_utils()->get_quiz_option(get_the_ID(), 'passing_grade', 0);

$attempt_remaining = $attempts_allowed - $attempted_count;

do_action('tutor_quiz/single/before/top'); ?>
<div class="tutor-quiz-top">
	<div class="tutor-quiz-top-left">
		<p><?php _e('Quiz', 'tutor'); ?> : <?php echo get_the_title(); ?></p>
		<p>
			<?php
			if ($course){
				?>
				<?php _e('Course', 'tutor'); ?> :
				<a href="<?php echo get_the_permalink($course->ID); ?>"><?php echo get_the_title($course->ID); ?></a>
			<?php } ?>
		</p>

		<p> <?php _e('Attempts Allowed', 'tutor'); ?> : <?php echo $attempts_allowed; ?> </p>
		<p> <?php echo sprintf(__('Passing Grade : %s', 'tutor'), $passing_grade.'%'); ?> </p>
	</div>

	<div class="tutor-quiz-top-right">
		<?php
		$total_questions = tutor_utils()->total_questions_for_student_by_quiz(get_the_ID());
		?>
		<p> <?php _e('Questions', 'tutor'); ?>: <?php echo $total_questions; ?></p>
		<?php
		$time_limit = tutor_utils()->get_quiz_option(get_the_ID(), 'time_limit.time_value');
		if ($time_limit){
			$time_type = tutor_utils()->get_quiz_option(get_the_ID(), 'time_limit.time_type');
			echo "<p> ".__('Time', 'tutor').": {$time_limit} {$time_type}</p>";
		}
		?>
		<p><?php _e('Attempted', 'tutor'); ?> : <?php echo $attempted_count; ?></p>
		<p><?php _e('Attempts Remaining', 'tutor'); ?> : <?php echo $attempt_remaining; ?></p>
	</div>
</div>
<?php do_action('tutor_quiz/single/after/top'); ?>
