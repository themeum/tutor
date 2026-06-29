<?php
/**
 * Quiz attempts feedback form.
 *
 * @package Tutor\Templates
 * @subpackage Dashboard\Quiz_Attempts
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\WPEditor;

if ( ! isset( $attempt_data ) || ! is_object( $attempt_data ) ) {
	return;
}

$attempt_info = isset( $attempt_data->attempt_info ) ? maybe_unserialize( $attempt_data->attempt_info ) : array();
$content      = is_array( $attempt_info ) ? (string) ( $attempt_info['instructor_feedback'] ?? '' ) : '';
?>

<div class="tutor-surface-base tutor-border-t">
	<section class="tutor-quiz-review-feedback-wrapper">
		<div> </div> <!-- This is placeholder for quiz summary sidebar -->
		<div class="tutor-quiz-review-feedback">
			<div class="tutor-flex tutor-flex-column tutor-gap-5">
				<h5 class="tutor-h5 tutor-font-semibold">
					<?php esc_html_e( 'Instructor Feedback', 'tutor' ); ?>
				</h5>

				<?php
				WPEditor::make()
					->name( 'feedback' )
					->id( 'tutor-quiz-attempt-feedback-editor' )
					->content( $content )
					->placeholder( __( 'Write your feedback', 'tutor' ) )
					->editor_config(
						array(
							'teeny'         => true,
							'quicktags'     => false,
							'editor_height' => 180,
						)
					)
					->attr(
						'x-bind',
						"register('feedback')"
					)
					->render();
				?>
			</div>

			<div class="tutor-flex tutor-justify-end">
				<?php
					Button::make()
						->label( __( 'Submit', 'tutor' ) )
						->variant( Variant::PRIMARY )
						->attr( 'type', 'submit' )
						->attr( ':disabled', 'feedbackMutation?.isPending || !Object.values(dirtyFields).some(Boolean)' )
						->attr( 'class', 'tutor-quiz-review-feedback-button' )
						->attr( ':class', "{ 'tutor-btn-loading': feedbackMutation?.isPending }" )
						->render();
				?>
			</div>
		</div>
	</section>
</div>
