<?php
/**
 * Template for Top Performing Course filter in Instructor dashboard.
 *
 * @package TutorPro\CourseBundle
 * @subpackage Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
?>

<div
	x-data="tutorPopover({
		placement: 'bottom-end',
		offset: 4,
	})"
>
	<button type="button" x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-outline tutor-btn-x-small tutor-gap-4">
		<div>
			<span class="tutor-text-subdued tutor-sm-hidden"><?php esc_html_e( 'By: ', 'tutor-pro' ); ?></span>
			<span class="tutor-text-normal"><?php echo esc_html( $options[ $selected ] ); ?></span>
		</div>
		<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN_2, 16, 16, array( 'class' => 'tutor-icon-secondary' ) ); ?>
	</button>
	<div 
		x-ref="content"
		x-show="open"
		x-cloak
		@click.outside="handleClickOutside()"
		class="tutor-popover"
	>
		<div class="tutor-popover-menu" style="min-width: 108px;">
			<?php foreach ( $options as $key => $option ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'type', $key ) ); ?>" class="tutor-popover-menu-item <?php echo $selected === $key ? 'tutor-active' : ''; ?>">
					<?php echo esc_html( $option ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</div>
