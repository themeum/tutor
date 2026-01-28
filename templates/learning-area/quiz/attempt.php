<?php
/**
 * Tutor learning area quiz active template
 *
 * This template get loaded once user submit the quiz or
 * if there any active quiz
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Button;

?>

<div class="tutor-quiz tutor-quiz-submission">
	<?php tutor_load_template( 'demo-components.learning-area.components.quiz.progress-bar' ); ?>
	<div class="tutor-quiz-questions">
		<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.true-false' ); ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.multiple-choice' ); ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.image-answering' ); ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.ordering' ); ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.matching' ); ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.fill-in-the-blanks' ); ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.openended-short-answer' ); ?>

		<?php Button::make()->label( __( 'Submit Quiz', 'tutor' ) )->render(); ?>
	</div>
</div>
