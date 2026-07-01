<?php
/**
 * Tutor learning area footer.
 *
 * @package Tutor\Templates
 * @subpackage LearningArea
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\Button;
use Tutor\Components\Constants\Color;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\SvgIcon;

global $tutor_course_id,
$tutor_current_post,
$tutor_is_enrolled,
$tutor_is_public_course;

$contents    = tutor_utils()->get_course_prev_next_contents_by_id( $tutor_current_post->ID );
$previous_id = $contents->previous_id;
$next_id     = $contents->next_id;

$prev_is_preview = get_post_meta( $previous_id, '_is_preview', true );
$next_is_preview = get_post_meta( $next_id, '_is_preview', true );

$prev_is_locked = ! ( $tutor_is_enrolled || $prev_is_preview || $tutor_is_public_course );
$next_is_locked = ! ( $tutor_is_enrolled || $next_is_preview || $tutor_is_public_course );

$prev_link = $prev_is_locked || ! $previous_id ? '#' : get_the_permalink( $previous_id );
$next_link = $next_is_locked || ! $next_id ? '#' : get_the_permalink( $next_id );

$is_completed_lesson = tutor_utils()->is_completed_lesson();

?>
<div class="tutor-learning-area-footer" data-lesson>
	<?php
	Button::make()
	->tag( 'a' )
	->variant( Variant::GHOST )
	->size( Size::SMALL )
	->label( __( 'Previous', 'tutor' ) )
	->icon( Icon::CHEVRON_LEFT_2 )
	->flip_rtl()
	->attr( 'href', esc_url( $prev_link ) )
	->attr( 'style', 'visibility:' . ( $previous_id ? 'visible' : 'hidden' ) )
	->disabled( $prev_is_locked )
	->render();

	if ( $tutor_is_enrolled ) {
		ob_start();
		?>
		<form method="post" class="tutor-mb-none" x-data="{ isLoading: false }">
			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce, false ); ?>
			<input type="hidden" value="<?php echo esc_attr( get_the_ID() ); ?>" name="lesson_id" />
			<input type="hidden" value="tutor_complete_lesson" name="tutor_action" />
			<button type="submit" name="complete_lesson_btn" @click="isLoading = true" :class="{ 'tutor-btn-loading': isLoading }" class="tutor-mark-as-complete-button <?php echo esc_attr( $is_completed_lesson ? 'completed' : '' ); ?>" <?php echo esc_attr( $is_completed_lesson ? 'disabled' : '' ); ?>>
				<span class="tutor-text-center tutor-w-full">
					<?php echo esc_html( $is_completed_lesson ? __( 'Completed', 'tutor' ) : __( 'Mark as Complete', 'tutor' ) ); ?>
				</span>
				<?php
				if ( $is_completed_lesson ) {
					SvgIcon::make()->name( Icon::LESSON_COMPLETED )->size( 40 )->render();
				} else {
					SvgIcon::make()->name( Icon::CHECK_2 )->size( 20 )->color( Color::BRAND )->render();
				}
				?>
			</button>
		</form>
		<?php
		echo apply_filters( 'tutor_learning_area_lesson_mark_as_complete', ob_get_clean() ); // phpcs:ignore --already sanitized.
	}

	Button::make()
	->tag( 'a' )
	->variant( Variant::GHOST )
	->size( Size::SMALL )
	->label( __( 'Next', 'tutor' ) )
	->icon( Icon::CHEVRON_RIGHT_2, 'right' )
	->flip_rtl()
	->attr( 'href', esc_url( $next_link ) )
	->attr( 'style', 'visibility:' . ( $next_id ? 'visible' : 'hidden' ) )
	->disabled( $next_is_locked )
	->render();
	?>
</div>
