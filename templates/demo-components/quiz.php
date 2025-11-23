<?php
/**
 * Tutor quiz.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

?>

<div class="tutor-quiz">
	<?php tutor_load_template( 'demo-components.learning-area.components.quiz.progress-bar' ); ?>
	
	<div class="tutor-quiz-questions">
		<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.true-false' ); ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.multiple-choice' ); ?>
	</div>
</div>
