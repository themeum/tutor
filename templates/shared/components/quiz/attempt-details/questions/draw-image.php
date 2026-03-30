<?php
/**
 * Attempt details Draw on Image (read-only).
 *
 * @package Tutor\Templates
 * @subpackage Shared\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Models\QuizModel;

if ( ! isset( $question ) || ! is_object( $question ) ) {
	return;
}

$attempt_answer = isset( $attempt_answer ) && is_object( $attempt_answer ) ? $attempt_answer : null;

$draw_image_answers = QuizModel::get_answers_by_quiz_question( (int) $question->question_id, false );

$instructor_answer_bg = null;

$instructor_answer_mask = null;

$ref_bg = '';

$ref_mask_raw = '';

if ( is_array( $draw_image_answers ) && ! empty( $draw_image_answers ) ) {
	foreach ( $draw_image_answers as $answer_row ) {
		if ( ! $instructor_answer_mask && ! empty( $answer_row->answer_two_gap_match ) ) {
			$instructor_answer_mask = $answer_row;
		}

		if ( ! $instructor_answer_bg ) {
			$maybe_bg_url = QuizModel::get_answer_image_url( $answer_row );
			if ( $maybe_bg_url ) {
				$instructor_answer_bg = $answer_row;
				$ref_bg               = $maybe_bg_url;
			}
		}

		if ( $instructor_answer_bg && $instructor_answer_mask ) {
			break;
		}
	}
}

$given_mask_raw = '';
if ( $attempt_answer && isset( $attempt_answer->given_answer ) ) {
	// Tutor Pro stores draw_image masks as a plain string (usually a local uploads URL)
	// in `given_answer`. Keep this compatible with Pro.
	$given_mask_raw = stripslashes( (string) $attempt_answer->given_answer );

	$given_mask_raw = trim( $given_mask_raw );

	// If mask was accidentally stored as serialized value, unwrap once.
	if ( '' === $given_mask_raw ) {
		$maybe_unserialized = maybe_unserialize( $attempt_answer->given_answer );
		if ( is_string( $maybe_unserialized ) ) {
			$given_mask_raw = trim( stripslashes( $maybe_unserialized ) );
		}
	}
}

$ref_mask_raw = $instructor_answer_mask && ! empty( $instructor_answer_mask->answer_two_gap_match ) ? trim( (string) $instructor_answer_mask->answer_two_gap_match ) : '';

/**
 * Normalize mask string for use in CSS mask-image url().
 *
 * @param string $mask Mask URL or data URI.
 * @return string Escaped fragment for url("...") or empty.
 */
$mask_to_css_url = static function ( $mask ) {
	$mask = trim( (string) $mask );
	if ( '' === $mask ) {
		return '';
	}
	// If it's a standard URL, normalize it for output.
	if ( false !== wp_http_validate_url( $mask ) ) {
		return esc_url_raw( $mask );
	}

	// Otherwise keep as-is (covers data URIs, relative paths, and other stored mask strings).
	return $mask;
};

$given_mask_css = $mask_to_css_url( $given_mask_raw );

$ref_mask_css = $mask_to_css_url( $ref_mask_raw );

$has_correct_mask = '' !== $ref_mask_css;

$has_student_mask = '' !== $given_mask_css;

$has_student_drawn = '' !== trim( (string) $given_mask_raw );

$has_bg = is_string( $ref_bg ) && '' !== trim( $ref_bg );

$show_combined = $has_bg && ( $has_correct_mask || $has_student_drawn );

$correct_mask_style = '';
if ( $has_correct_mask ) {
	$correct_mask_style = '--tutor-draw-mask-url: url("' . $ref_mask_css . '"); --tutor-draw-mask-bg: rgba(4, 201, 134, 0.28);';
}
$student_mask_style = '';
if ( $has_student_drawn && $has_student_mask ) {
	$student_mask_style = '--tutor-draw-mask-url: url("' . $given_mask_css . '"); --tutor-draw-mask-bg: rgba(233, 62, 62, 0.2);';
}

?>

<div class="tutor-quiz-question-options tutor-quiz-draw-image-review">
	<?php if ( $show_combined ) : ?>
		<?php if ( ! $has_student_drawn ) : ?>
			<p class="tutor-fs-7 tutor-color-secondary tutor-mb-8">
				<?php esc_html_e( 'No drawing submitted.', 'tutor' ); ?>
			</p>
		<?php endif; ?>
		<?php if ( $has_correct_mask || $has_student_drawn ) : ?>
			<ul class="tutor-draw-image-review-legend tutor-fs-7 tutor-fw-medium tutor-color-black tutor-mb-8" role="list">
				<?php if ( $has_correct_mask ) : ?>
					<li class="tutor-draw-image-review-legend__item">
						<span class="tutor-draw-image-review-swatch tutor-draw-image-review-swatch--correct" aria-hidden="true"></span>
						<?php esc_html_e( 'Correct answer zone', 'tutor' ); ?>
					</li>
				<?php endif; ?>
				<?php if ( $has_student_drawn ) : ?>
					<li class="tutor-draw-image-review-legend__item">
						<span class="tutor-draw-image-review-swatch tutor-draw-image-review-swatch--student" aria-hidden="true"></span>
						<?php esc_html_e( 'Your answer', 'tutor' ); ?>
					</li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>
		<div class="tutor-draw-image-review-inner">
			<img src="<?php echo esc_url( $ref_bg ); ?>" alt="" class="tutor-draw-image-bg" />
			<?php if ( $has_correct_mask ) : ?>
				<span
					class="tutor-draw-image-review-mask tutor-draw-image-review-mask--correct"
					style="<?php echo esc_attr( $correct_mask_style ); ?>"
					role="presentation"
				></span>
			<?php endif; ?>
			<?php if ( $has_student_drawn ) : ?>
				<span
					class="tutor-draw-image-review-mask tutor-draw-image-review-mask--student"
					style="<?php echo esc_attr( $student_mask_style ); ?>"
					role="presentation"
				></span>
			<?php endif; ?>
		</div>
	<?php elseif ( $has_student_drawn || $has_correct_mask ) : ?>
		<?php if ( $has_student_drawn ) : ?>
			<div class="tutor-draw-image-given-answer tutor-mb-12">
				<p class="tutor-fs-7 tutor-fw-medium tutor-color-black tutor-mb-8">
					<?php esc_html_e( 'Your drawing:', 'tutor' ); ?>
				</p>
				<div class="tutor-draw-image-review-fallback">
					<?php
					$given_src_for_img = trim( $given_mask_raw );
					?>
					<img
						src="<?php echo 0 === strpos( $given_src_for_img, 'data:image/' ) ? esc_attr( $given_src_for_img ) : esc_url( $given_src_for_img ); ?>"
						alt=""
						class="tutor-draw-image-single"
					/>
				</div>
			</div>
		<?php else : ?>
			<p class="tutor-fs-7 tutor-color-secondary tutor-mb-12">
				<?php esc_html_e( 'No drawing submitted.', 'tutor' ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $has_correct_mask ) : ?>
			<div class="tutor-draw-image-correct-answer tutor-mt-12">
				<p class="tutor-fs-7 tutor-fw-medium tutor-color-black tutor-mb-8">
					<?php esc_html_e( 'Reference (correct answer zones):', 'tutor' ); ?>
				</p>
				<div class="tutor-draw-image-review-fallback">
					<?php
					$ref_src_for_img = trim( $ref_mask_raw );
					?>
					<img
						src="<?php echo 0 === strpos( $ref_src_for_img, 'data:image/' ) ? esc_attr( $ref_src_for_img ) : esc_url( $ref_src_for_img ); ?>"
						alt=""
						class="tutor-draw-image-single"
					/>
				</div>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<p class="tutor-fs-7 tutor-color-secondary tutor-mb-12">
			<?php esc_html_e( 'No drawing submitted.', 'tutor' ); ?>
		</p>
	<?php endif; ?>
</div>
