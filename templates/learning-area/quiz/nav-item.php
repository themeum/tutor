<?php
/**
 * Show quiz nav item on the learning area
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Models\QuizModel;

global $tutor_current_content_id;

$quiz       = $quiz ?? null;
$can_access = $can_access ?? false;

if ( ! $quiz && ! is_a( $quiz, 'WP_Post' ) ) {
	return;
}

$quiz_title = $quiz->post_title;

$active_class   = $tutor_current_content_id === $quiz->ID ? 'active' : '';
$disabled_class = $can_access ? '' : 'disabled';

$icon_name = Icon::QUIZ_2;
if ( ! $can_access ) {
	$icon_name = Icon::LOCK_STROKE_2;
} else {
	$last_attempt  = ( new QuizModel() )->get_first_or_last_attempt( $quiz->ID );
	$attempt_ended = is_object( $last_attempt ) && QuizModel::ATTEMPT_STARTED !== $last_attempt->attempt_status;
	$result_class  = '';

	$quiz_result = QuizModel::get_quiz_result( $quiz->ID );
	if ( $attempt_ended && QuizModel::ATTEMPT_STARTED !== $last_attempt->attempt_status ) {
		if ( 'fail' === $quiz_result ) {
			$icon_name    = Icon::CROSS_CIRCLE_LINE;
			$result_class = 'fail';
		} elseif ( 'pending' === $quiz_result ) {
			$icon_name    = Icon::INFO_2;
			$result_class = 'pending';
		} elseif ( 'pass' === $quiz_result ) {
			$icon_name = Icon::COMPLETED_COLORIZE;
		}
	}
}
?>

<a
	href="<?php echo esc_url( $can_access ? get_permalink( $quiz->ID ) : '#' ); ?>" 
	title="<?php echo esc_attr( $quiz_title ); ?>"
	class="<?php echo esc_html( sprintf( 'tutor-learning-nav-item %s %s', $active_class, $disabled_class ) ); ?>"
	<?php echo ! $can_access ? 'aria-disabled="true"' : ''; ?>
>
	<?php SvgIcon::make()->name( $icon_name )->attr( 'class', $result_class )->size( 20 )->render(); ?>
	<div class="tutor-overflow-hidden">
		<div class="tutor-truncate"><?php echo esc_html( $quiz_title ); ?></div>
		<div class="tutor-tiny-2 tutor-text-subdued"><?php esc_html_e( 'Quiz', 'tutor' ); ?></div>
	</div>
</a>
