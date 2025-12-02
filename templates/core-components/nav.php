<?php
/**
 * Tutor navigation component.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

/**
 * Expected $args structure:
 *
 * $args = array(
 *     'items' => array(
 *         array(
 *             'type'     => 'link',        // 'link' or 'dropdown'
 *             'label'    => 'Wishlist',
 *             'icon'     => Icon::WISHLIST,
 *             'url'      => '#',
 *             'active'   => false,
 *         ),
 *         array(
 *             'type'    => 'dropdown',
 *             'icon'    => Icon::ENROLLED,
 *             'active'  => true,
 *             'options' => array(
 *                 array(
 *                     'label'  => 'Active',
 *                     'icon'   => Icon::PLAY_LINE,
 *                     'url'    => '#',
 *                     'active' => false,
 *                 ),
 *                 array(
 *                     'label'  => 'Enrolled',
 *                     'icon'   => Icon::ENROLLED,
 *                     'url'    => '#',
 *                     'active' => true,
 *                 ),
 *             ),
 *         ),
 *     ),
 * );
 */

if ( empty( $items ) ) {
	return;
}

/**
 * Get the label of the active dropdown option.
 *
 * @since 4.0.0
 *
 * @param array $options Array of dropdown options.
 * @return string The label of the active option, or the first option's label if none are active.
 */
function get_active_dropdown_label( $options ) {
	foreach ( $options as $option ) {
		if ( ! empty( $option['active'] ) ) {
			return $option['label'] ?? '';
		}
	}
	return $options[0]['label'] ?? '';
}
?>

<ul class="tutor-nav">
	<?php foreach ( $items as $item ) : ?>
		<li class="tutor-nav-item">
			<?php if ( 'dropdown' === ( $item['type'] ?? 'link' ) ) : ?>
				<?php
				$options      = $item['options'] ?? array();
				$active_label = get_active_dropdown_label( $options );
				?>
				<div x-data="tutorPopover({ placement: 'bottom-start', offset: 4 })">
					<button 
						x-ref="trigger" 
						@click="toggle()" 
						class="tutor-nav-link <?php echo ! empty( $item['active'] ) ? 'active' : ''; ?>"
					>
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<?php tutor_utils()->render_svg_icon( $item['icon'], 20, 20 ); ?>
						<?php endif; ?>
						<?php echo esc_html( $active_label ); ?>
						<?php
						tutor_utils()->render_svg_icon(
							Icon::CHEVRON_DOWN,
							20,
							20,
							array( 'class' => 'tutor-icon-subdued' )
						);
						?>
					</button>

					<div 
						x-ref="content"
						x-show="open"
						x-cloak
						@click.outside="handleClickOutside()"
						class="tutor-popover tutor-nav-dropdown"
					>
						<ul>
							<?php foreach ( $options as $option ) : ?>
								<li>
									<a 
										href="<?php echo esc_url( $option['url'] ?? '#' ); ?>" 
										class="tutor-nav-dropdown-link <?php echo ! empty( $option['active'] ) ? 'active' : ''; ?>"
									>
										<?php if ( ! empty( $option['icon'] ) ) : ?>
											<?php tutor_utils()->render_svg_icon( $option['icon'], 20, 20 ); ?>
										<?php endif; ?>
										<?php echo esc_html( $option['label'] ?? '' ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php else : ?>
				<a 
					href="<?php echo esc_url( $item['url'] ?? '#' ); ?>" 
					class="tutor-nav-link <?php echo ! empty( $item['active'] ) ? 'active' : ''; ?>"
				>
					<?php if ( ! empty( $item['icon'] ) ) : ?>
						<?php tutor_utils()->render_svg_icon( $item['icon'], 20, 20 ); ?>
					<?php endif; ?>
					<?php echo esc_html( $item['label'] ?? '' ); ?>
				</a>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
