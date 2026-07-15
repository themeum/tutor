<?php
/**
 * Template for Top Performing Course filter in Instructor dashboard.
 *
 * @package Tutor\Templates
 * @subpackage Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Components\Constants\Color;
?>

<div
	x-data="tutorPopover({
		placement: 'bottom-end',
		offset: 4,
	})"
	x-transition.origin.top.right
>
	<button type="button" x-ref="trigger" @click="toggle()" class="tutor-btn tutor-btn-outline tutor-btn-x-small tutor-gap-4">
		<div>
			<span class="tutor-text-subdued tutor-sm-hidden"><?php esc_html_e( 'By: ', 'tutor' ); ?></span>
			<span class="tutor-text-normal"><?php echo esc_html( $options[ $selected ] ); ?></span>
		</div>
		<?php SvgIcon::make()->name( Icon::CHEVRON_DOWN_2 )->size( 16 )->color( Color::SECONDARY )->render(); ?>
	</button>
	<div 
		x-ref="content"
		x-show="open"
		x-cloak
		@click.outside="handleClickOutside()"
		class="tutor-popover"
		x-transition.origin.top.right
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
