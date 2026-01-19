<?php
/**
 * Upcoming Task Item Component
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$get_icon_by_post_type = function ( $post_type ) {
	switch ( $post_type ) {
		case 'tutor_assignments':
			return Icon::ASSIGNMENT;
		case 'tutor-google-meet':
			return Icon::GOOGLE_MEET;
		case 'tutor_quiz':
			return Icon::QUIZ;
		case 'tutor_zoom_meeting':
			return Icon::ZOOM;
		case 'lesson':
			return Icon::LESSON;
	}
};

?>

<div class="tutor-dashboard-home-task">
	<div class="tutor-dashboard-home-task-icon">
		<?php tutor_utils()->render_svg_icon( $get_icon_by_post_type( $item['post_type'] ) ); ?>
	</div>
	<div class="tutor-flex tutor-flex-column tutor-mt-1">
		<div class="tutor-flex tutor-items-center tutor-gap-2 tutor-tiny tutor-text-secondary">
			<span class="tutor-text-secondary">
				<?php if ( gmdate( 'Y-m-d' ) === $item['date'] ) : ?>
					<?php esc_html_e( 'Today', 'tutor' ); ?>
				<?php else : ?>
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), $item['date'] ) ); ?>
				<?php endif; ?>
			</span>
			<span class="tutor-icon-secondary">•</span>
			<span class="tutor-text-secondary">
				<?php echo esc_html( date_i18n( get_option( 'time_format' ), $item['date'] ) ); ?>
			</span>
		</div>
		<div class="tutor-small tutor-font-medium">
			<?php echo esc_html( $item['name'] ); ?>
		</div>
		<div class="tutor-badge tutor-mt-5" data-meta>
			<?php echo esc_html( $item['meta_info'] ); ?>
		</div>
		<a href="<?php echo esc_url( $item['url'] ); ?>" class="tutor-dashboard-home-task-link" data-link>
			<?php esc_html_e( 'Open', 'tutor' ); ?>
			<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_RIGHT_2 ); ?>
		</a>
	</div>
</div>
