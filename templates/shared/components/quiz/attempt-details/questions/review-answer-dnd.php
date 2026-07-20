<?php
/**
 * Shared read-only review template for DnD-like question types.
 *
 * Supports: image_answering, ordering, matching, image_matching.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea\Quiz\AttemptDetails
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Models\QuizModel;

if ( ! isset( $question ) || ! is_object( $question ) ) {
	return;
}

$question_settings = maybe_unserialize( $question->question_settings );
$question_settings = is_array( $question_settings ) ? $question_settings : array();
$question_type     = (string) ( $question->question_type ?? '' );
$is_image_matching = 'image_matching' === $question_type || '1' === (string) ( $question_settings['is_image_matching'] ?? '0' );

if ( $is_image_matching ) {
	$question_type = 'matching';
}

$question_settings['show_question_mark'] = $question_settings['show_question_mark'] ?? '1';
$question_settings['is_image_matching']  = $is_image_matching ? '1' : ( $question_settings['is_image_matching'] ?? '0' );
$question_answers                        = QuizModel::get_answers_by_quiz_question( (int) $question->question_id );
$question_answers                        = is_array( $question_answers ) ? array_values( $question_answers ) : array();
$given_raw                               = $question->given_answer ?? null;
$given_answer                            = maybe_unserialize( $given_raw );

$normalize = static function ( $value ) {
	return strtolower( trim( wp_strip_all_tags( (string) $value ) ) );
};

$get_answer_by_id = static function ( $answer_id ) {
	$results = tutor_utils()->get_answer_by_id( (int) $answer_id );
	return is_array( $results ) && ! empty( $results ) ? $results[0] : null;
};

$get_image_url = static function ( $answer ) {
	if ( ! empty( $answer->image_id ) ) {
		return wp_get_attachment_image_url( $answer->image_id, 'thumbnail' );
	}
	return '';
};

$rows = array();

if ( 'ordering' === $question_type ) {
	$given_ids = is_array( $given_answer ) ? array_values( $given_answer ) : array();
	foreach ( $question_answers as $idx => $correct_item ) {
		$selected_item = isset( $given_ids[ $idx ] ) ? $get_answer_by_id( $given_ids[ $idx ] ) : null;
		$is_row_ok     = $selected_item && (int) $selected_item->answer_id === (int) $correct_item->answer_id;

		$rows[] = array(
			'given_text'    => $selected_item->answer_title ?? '',
			'given_image'   => $selected_item ? $get_image_url( $selected_item ) : '',
			'correct_text'  => $correct_item->answer_title ?? '',
			'correct_image' => $get_image_url( $correct_item ),
			'given_status'  => $is_row_ok ? 'correct' : 'incorrect',
		);
	}
} elseif ( 'matching' === $question_type ) {
	$given_ids = is_array( $given_answer ) ? array_values( $given_answer ) : array();
	foreach ( $question_answers as $idx => $correct_item ) {
		$selected_item = isset( $given_ids[ $idx ] ) ? $get_answer_by_id( $given_ids[ $idx ] ) : null;
		$given_text    = $is_image_matching ? ( $selected_item->answer_title ?? '' ) : ( $selected_item->answer_two_gap_match ?? '' );
		$correct_text  = $is_image_matching ? ( $correct_item->answer_title ?? '' ) : ( $correct_item->answer_two_gap_match ?? '' );
		$is_row_ok     = $normalize( $given_text ) === $normalize( $correct_text );

		$rows[] = array(
			'given_text'    => $given_text,
			'given_image'   => $is_image_matching && $selected_item ? $get_image_url( $selected_item ) : '',
			'correct_text'  => $correct_text,
			'correct_image' => $is_image_matching ? $get_image_url( $correct_item ) : '',
			'given_status'  => $is_row_ok ? 'correct' : 'incorrect',
		);
	}
} elseif ( 'image_answering' === $question_type ) {
	$given_map      = is_array( $given_answer ) ? $given_answer : array();
	$answer_status  = QuizModel::get_attempt_answer_status( $question );
	$row_item_state = 'correct' === $answer_status ? 'correct' : ( 'pending' === $answer_status ? 'pending' : 'incorrect' );

	foreach ( $question_answers as $correct_item ) {
		$answer_id    = (int) ( $correct_item->answer_id ?? 0 );
		$given_text   = (string) ( $given_map[ $answer_id ] ?? '' );
		$correct_text = (string) ( $correct_item->answer_title ?? '' );

		$rows[] = array(
			'given_text'    => $given_text,
			'given_image'   => $get_image_url( $correct_item ),
			'correct_text'  => $correct_text,
			'correct_image' => $get_image_url( $correct_item ),
			'given_status'  => $row_item_state,
		);
	}
}
?>

<div class="tutor-quiz-review-dnd-grid">
	<div class="tutor-quiz-review-dnd-head">
		<div class="tutor-quiz-review-col-title tutor-quiz-review-given"><?php esc_html_e( 'Given Answer', 'tutor' ); ?></div>
		<div class="tutor-quiz-review-col-title tutor-quiz-review-correct"><?php esc_html_e( 'Correct Answer', 'tutor' ); ?></div>
	</div>

	<div class="tutor-quiz-review-dnd-rows">
		<?php
		foreach ( $rows as $row ) :
			$given_text   = isset( $row['given_text'] ) ? wp_unslash( $row['given_text'] ) : '';
			$correct_text = isset( $row['correct_text'] ) ? wp_unslash( $row['correct_text'] ) : '';
			$given_status = isset( $row['given_status'] ) ? $row['given_status'] : 'neutral';
			?>
			<div class="tutor-quiz-review-dnd-row">
				<div class="tutor-quiz-review-item tutor-quiz-review-given" data-option="<?php echo esc_attr( $given_status ); ?>">
					<?php if ( ! empty( $row['given_image'] ) ) : ?>
						<img src="<?php echo esc_url( $row['given_image'] ); ?>" alt="<?php echo esc_attr( $given_text ); ?>">
					<?php endif; ?>
					<span><?php echo esc_html( $given_text ); ?></span>
				</div>
				<div class="tutor-quiz-review-item tutor-quiz-review-correct" data-option="neutral">
					<?php if ( ! empty( $row['correct_image'] ) ) : ?>
						<img src="<?php echo esc_url( $row['correct_image'] ); ?>" alt="<?php echo esc_attr( $correct_text ); ?>">
					<?php endif; ?>
					<span><?php echo esc_html( $correct_text ); ?></span>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
