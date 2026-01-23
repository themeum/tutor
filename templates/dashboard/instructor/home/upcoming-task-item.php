<?php
/**
 * Upcoming Task Item Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\Badge;

$get_icon_by_post_type = function ( $post_type ) {
	switch ( $post_type ) {
		case 'tutor_assignments':
			return Icon::ASSIGNMENT;
		case 'tutor-google-meet':
			return Icon::GOOGLE_MEET_COLORIZE;
		case 'tutor_quiz':
			return Icon::QUIZ;
		case 'tutor_zoom_meeting':
			return Icon::ZOOM_COLORIZE;
		case 'lesson':
			return Icon::LESSON;
	}
};

$label = __( 'Live Session', 'tutor' );
?>

<div class="tutor-dashboard-home-task">
	<div class="tutor-dashboard-home-task-icon">
		<?php tutor_utils()->render_svg_icon( Icon::VIDEO_CAMERA ); ?>
	</div>
	<div class="tutor-flex tutor-flex-column tutor-mt-1">
		<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-tiny tutor-text-secondary">
			<span class="tutor-text-secondary">
				<?php if ( gmdate( 'Y-m-d' ) === gmdate( 'Y-m-d', strtotime( $item['date'] ) ) ) : ?>
					<?php esc_html_e( 'Today', 'tutor' ); ?>
				<?php else : ?>
					<?php echo esc_html( gmdate( 'F d, Y', strtotime( $item['date'] ) ) ); ?>
				<?php endif; ?>
			</span>
			<span class="tutor-icon-secondary">•</span>
			<span class="tutor-text-secondary">
				<?php echo esc_html( gmdate( 'h:i A', strtotime( $item['date'] ) ) ); ?>
			</span>
		</div>
		<div class="tutor-small tutor-font-medium">
			<?php echo esc_html( $item['name'] ); ?>
		</div>
		<div class="tutor-dashboard-home-task-live-tag" data-meta>
			<div class="tutor-dashboard-home-task-live-tag-badge">
				<?php Badge::make() // phpcs:ignore
					->icon( $get_icon_by_post_type( $item['post_type'] ) )
					->label( $label )
					->rounded()
					->render();
				?>
			</div>
		</div>
		<a href="<?php echo esc_url( $item['url'] ); ?>" class="tutor-dashboard-home-task-link" data-link>
			<?php esc_html_e( 'Open', 'tutor' ); ?>
			<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT_2 ); ?>
		</a>
	</div>
</div>
