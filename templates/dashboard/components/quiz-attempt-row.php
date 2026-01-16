<?php
/**
 * Tutor dashboard quiz attempt row.
 * Reusable component for displaying a single quiz attempt row.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Components\Badge;
use Tutor\Components\Button;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Popover;
use Tutor\Components\PreviewTrigger;
use TUTOR\Icon;
use Tutor\Models\QuizModel;

if ( empty( $attempt ) ) {
	return;
}


$show_quiz_title = $show_quiz_title ?? false;
$show_course     = $show_course ?? false;
$attempt_number  = $attempt_number ?? null;
$attempts_count  = $attempts_count ?? 0;
$badge           = Badge::make()->label( 'Failed' )->variant( Variant::CANCELLED )->circle();
$kebab_button    = Button::make()
					->icon( Icon::THREE_DOTS_VERTICAL )
					->attr( 'x-ref', 'trigger' )
					->attr( '@click', 'toggle()' )
					->attr( 'class', 'tutor-quiz-item-result-more' )
					->variant( 'secondary' )
					->size( Size::X_SMALL )
					->get();

$review_url     = add_query_arg( array( 'view_quiz_attempt_id' => $attempt_id ), get_pagenum_link() );
$click_attr     = sprintf( 'hide(); TutorCore.modal.showModal("tutor-quiz-attempt-delete-modal", { attemptID: %d });', $attempt_id );
$delete_attr    = sprintf( 'TutorCore.modal.showModal("tutor-quiz-attempt-delete-modal", { attemptID: %d });', $attempt_id );
$details_button = Button::make()->label( __( 'Details', 'tutor' ) )
					->icon( Icon::RESOURCES, 'left', 20, 20 )
					->size( Size::MEDIUM )
					->tag( 'a' )
					->attr( 'href', $review_url )
					->variant( 'primary' );

$delete_button = Button::make()->label( __( 'Delete', 'tutor' ) )
					->icon( Icon::DELETE_2, 'left', 20, 20 )
					->size( Size::MEDIUM )
					->attr( '@click', $delete_attr )
					->variant( 'secondary' );


?>
<div class="tutor-quiz-attempts-item">
	<div class="tutor-quiz-item-info">
		<?php if ( $show_quiz_title && ! empty( $quiz_title ) ) : ?>
			<div class="tutor-flex tutor-items-start tutor-justify-start tutor-gap-4">
				<div class="tutor-quiz-item-info-title"><?php echo esc_html( $quiz_title ); ?></div>
				<?php if ( $attempts_count > 1 ) : ?>
					<button @click="expanded = !expanded" class="tutor-quiz-attempts-expand-btn">
						<?php
						printf(
							/* translators: %d: number of attempts */
							esc_html__( '%d Attempts', 'tutor' ),
							esc_attr( $attempts_count )
						);
						?>
						<span class="tutor-quiz-attempts-expand-icon">
							<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 18, 18 ); ?>
						</span>
					</button>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $attempt_number ) : ?>
			<div class="tutor-quiz-item-info-title">
				<?php
				/* translators: %d: attempt number */
				echo esc_html( sprintf( __( 'Attempt %d', 'tutor' ), $attempt_number ) );
				?>
			</div>
		<?php endif; ?>

		<div class="tutor-quiz-item-info-course">
			<?php esc_html_e( 'Course:', 'tutor' ); ?> 
			<?php
			PreviewTrigger::make()
				->id( $course_id ?? 0 )
				->render()
			?>
		</div>

		<div class="tutor-quiz-item-info-date tutor-text-subdued"><?php echo esc_html( $attempt['date'] ?? '' ); ?></div>
		<?php if ( ! empty( $attempt['student'] ) ) : ?>
		<div class="tutor-quiz-item-info-date tutor-text-subdued"><?php echo esc_html__( 'Student Name: ', 'tutor' ) . esc_html( $attempt['student'] ); ?></div>
		<?php endif; ?>
	</div>

	<div class="tutor-quiz-item-marks">
		<div x-data="tutorStatics({ value: <?php echo esc_attr( $attempt['marks_percent'] ?? 0 ); ?>, type: 'progress' })">
			<div x-html="render()"></div>
		</div>
		<div class="tutor-quiz-marks-breakdown">
			<div class="tutor-quiz-marks-correct">
				<?php
				/* translators: %d: number of correct answers */
				echo esc_html( sprintf( __( '%d correct', 'tutor' ), $attempt['correct_answers'] ?? 0 ) );
				?>
			</div>
			<div class="tutor-quiz-marks-incorrect">
				<?php
				/* translators: %d: number of incorrect answers */
				echo esc_html( sprintf( __( '%d incorrect', 'tutor' ), $attempt['incorrect_answers'] ?? 0 ) );
				?>
			</div>
		</div>
	</div>

	<div class="tutor-quiz-item-time">
		<?php tutor_utils()->render_svg_icon( Icon::STOPWATCH, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
		<?php echo esc_html( $attempt['time_taken'] ?? '' ); ?>
	</div>

	<div class="tutor-quiz-item-result">
		<?php
		if ( QuizModel::RESULT_PASS === $attempt['result'] ) {
			$badge
				->label( __( 'Passed', 'tutor' ) )
				->variant( Variant::COMPLETED )
				->render();
		} elseif ( QuizModel::RESULT_PENDING === $attempt['result'] ) {
			$badge
				->label( __( 'Pending', 'tutor' ) )
				->variant( Variant::PENDING )
				->render();
		} else {
			$badge->render();
		}


		Popover::make()
			->trigger( $kebab_button )
			->placement( 'bottom' )
			->menu_item(
				array(
					'tag'     => 'a',
					'content' => __( 'Details', 'tutor' ),
					'icon'    => tutor_utils()->get_svg_icon( Icon::RESOURCES ),
					'attr'    => array( 'href' => $review_url ),
				)
			)
			->menu_item(
				array(
					'tag'     => 'a',
					'content' => __( 'Delete', 'tutor' ),
					'icon'    => tutor_utils()->get_svg_icon( Icon::DELETE_2 ),
					'attr'    => array(
						'href'   => '#',
						'@click' => $click_attr,
					),
				)
			)
			->render()
		?>
		
	</div>
</div>
<div class="tutor-quiz-item-actions tutor-flex tutor-justify-between">
	<?php $details_button->render(); ?>
	<?php $delete_button->render(); ?>
</div>
