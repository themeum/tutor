<?php
/**
 * Tutor quiz.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>

<div class="tutor-quiz-summary-page">
	<div class="tutor-quiz-summary-header">
		<div class="tutor-quiz-summary-header-inner">
			<div class="tutor-flex tutor-items-center tutor-gap-4">
				<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
					<?php tutor_utils()->render_svg_icon( Icon::ARROW_LEFT_2 ); ?>
				</button>
				<h5 class="tutor-h5 tutor-font-semibold"><?php esc_html_e( 'Quiz Summary', 'tutor' ); ?></h5>
			</div>
			<button type="button" class="tutor-btn tutor-btn-ghost tutor-btn-x-small tutor-btn-icon">
				<?php tutor_utils()->render_svg_icon( Icon::CROSS ); ?>
			</button>
		</div>
	</div>

	<div class="tutor-surface-l1">
		<?php tutor_load_template( 'demo-components.learning-area.components.quiz.summary' ); ?>
	</div>

	<div class="tutor-quiz-summary-body">
		<div class="tutor-quiz-summary-sidebar">
			<h3 class="tutor-h3 tutor-mb-10">
				<?php esc_html_e( 'Quiz questions', 'tutor' ); ?>
			</h3>

			<!-- @TODO: Need to implement the sticky sidebar functions. -->
			<div class="tutor-quiz-sidebar-questions">
				<a href="#" class="tutor-quiz-sidebar-question-item active correct">
					<div class="tutor-question-number">1.</div>
					<div class="tutor-question-content">
						<span>True or False:</span> You can only join the cybersecurity industry if you have a strong technical and engineering background.
					</div>
				</a>
				<a href="#" class="tutor-quiz-sidebar-question-item correct">
					<div class="tutor-question-number">2.</div>
					<div class="tutor-question-content">
						Fill in the text!
					</div>
				</a>
				<a href="#" class="tutor-quiz-sidebar-question-item incorrect">
					<div class="tutor-question-number">3.</div>
					<div class="tutor-question-content">
						Which of the following is closest to what cybersecurity is?
					</div>
				</a>
				<a href="#" class="tutor-quiz-sidebar-question-item correct">
					<div class="tutor-question-number">4.</div>
					<div class="tutor-question-content">
						<span>True or False:</span> You can only join the cybersecurity industry if you have a strong technical and engineering background.
					</div>
				</a>
				<a href="#" class="tutor-quiz-sidebar-question-item incorrect">
					<div class="tutor-question-number">5.</div>
					<div class="tutor-question-content">
						Fill in the text!
					</div>
				</a>
				<a href="#" class="tutor-quiz-sidebar-question-item incorrect">
					<div class="tutor-question-number">6.</div>
					<div class="tutor-question-content">
						<span>Find the Bug:</span> What is incorrect about the following code block?
					</div>
				</a>
			</div>
		</div>
		<div class="tutor-quiz-summary-content">
			<h3 class="tutor-h3 tutor-sm-text-h5 tutor-text-subdued tutor-mb-10 tutor-sm-mb-5">
				<?php esc_html_e( 'Review your answers', 'tutor' ); ?>
			</h3>
			<div class="tutor-quiz-questions">
				<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.true-false' ); ?>
				<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.multiple-choice' ); ?>
				<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.image-answering' ); ?>
				<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.ordering' ); ?>
				<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.matching' ); ?>
				<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.fill-in-the-blanks' ); ?>
				<?php tutor_load_template( 'demo-components.learning-area.components.quiz.questions.openended-short-answer' ); ?>
			</div>
		</div>
	</div>
</div>
