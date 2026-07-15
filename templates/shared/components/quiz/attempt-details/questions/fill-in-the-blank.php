<?php
/**
 * Attempt details Fill in the Blank (read-only).
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

$question_answers = QuizModel::get_answers_by_quiz_question( (int) $question->question_id );
$question_answers = is_array( $question_answers ) ? $question_answers : array();
$given_values     = array();

if ( isset( $question->given_answer ) ) {
	$given_raw = maybe_unserialize( $question->given_answer );
	if ( is_array( $given_raw ) ) {
		$given_values = array_values( array_map( 'strval', $given_raw ) );
	} elseif ( is_string( $given_raw ) && '' !== trim( $given_raw ) ) {
		$given_values = array( (string) $given_raw );
	}
}

$normalize = static function ( $value ) {
	return strtolower( trim( wp_strip_all_tags( (string) $value ) ) );
};

$render_sentence = static function ( string $text, array $values, string $default_status = 'correct' ) {
	$idx = 0;

	$sentence = preg_replace_callback(
		'/{dash}/',
		function () use ( &$idx, $values, $default_status ) {
			$item   = $values[ $idx ] ?? array(
				'value'  => '',
				'status' => $default_status,
			);
			$value  = (string) ( $item['value'] ?? '' );
			$status = (string) ( $item['status'] ?? $default_status );
			$idx++;

			return sprintf(
				"<span class='tutor-quiz-question-input' data-option='%s'>%s</span>",
				esc_attr( $status ),
				esc_html( $value )
			);
		},
		$text
	);

	return wp_kses(
		$sentence,
		array(
			'span' => array(
				'class'       => true,
				'data-option' => true,
			),
		)
	);
};

$given_cursor       = 0;
$given_markup_lines = array();
$correct_lines      = array();
?>

<?php foreach ( $question_answers as $answer_item ) : ?>
	<?php
	$answer_title    = stripslashes( (string) ( $answer_item->answer_title ?? '' ) );
	$dash_count      = substr_count( $answer_title, '{dash}' );
	$correct_tokens  = array_map( 'trim', explode( '|', (string) ( $answer_item->answer_two_gap_match ?? '' ) ) );
	$given_tokens    = array_slice( $given_values, $given_cursor, $dash_count );
	$given_token_set = array();
	$correct_set     = array();

	for ( $i = 0; $i < $dash_count; $i++ ) {
		$given_text   = isset( $given_tokens[ $i ] ) ? (string) $given_tokens[ $i ] : '';
		$correct_text = isset( $correct_tokens[ $i ] ) ? (string) $correct_tokens[ $i ] : '';
		$is_match     = '' !== trim( $given_text ) && $normalize( $given_text ) === $normalize( $correct_text );

		$given_token_set[] = array(
			'value'  => $given_text,
			'status' => $is_match ? 'correct' : 'incorrect',
		);
		$correct_set[]     = array(
			'value'  => $correct_text,
			'status' => 'correct',
		);
	}

	$given_cursor        += $dash_count;
	$given_markup_lines[] = $render_sentence( $answer_title, $given_token_set, 'incorrect' );
	$correct_lines[]      = $render_sentence( $answer_title, $correct_set, 'correct' );
	?>
<?php endforeach; ?>

<div class="tutor-quiz-question-options">
	<div class="tutor-quiz-question-option" data-readonly="true">
		<div class="tutor-quiz-review-col-title"><?php esc_html_e( 'Given Answer', 'tutor' ); ?></div>
		<?php foreach ( $given_markup_lines as $line ) : ?>
			<p><?php echo wp_kses_post( $line ); ?></p>
		<?php endforeach; ?>
	</div>
	<div class="tutor-quiz-question-option" data-readonly="true">
		<div class="tutor-quiz-review-col-title"><?php esc_html_e( 'Correct Answer', 'tutor' ); ?></div>
		<?php foreach ( $correct_lines as $line ) : ?>
			<p><?php echo wp_kses_post( $line ); ?></p>
		<?php endforeach; ?>
	</div>
</div>
