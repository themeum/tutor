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
 *     'variant' => 'primary', // 'primary' or 'secondary' (default: 'primary')
 *     'size'    => 'md',      // 'sm', 'md', or 'lg' (default: 'md')
 * );
 */

if ( empty( $items ) ) {
	return;
}

// Get size variant (default to 'md').
$size          = $size ?? 'md';
$allowed_sizes = array( 'sm', 'md', 'lg' );
$size          = in_array( $size, $allowed_sizes, true ) ? $size : 'md';

// Get style variant (default to 'primary').
$variant          = $variant ?? 'primary';
$allowed_variants = array( 'primary', 'secondary' );
$variant          = in_array( $variant, $allowed_variants, true ) ? $variant : 'primary';

// Icon sizes based on variant.
$icon_sizes = array(
	'sm' => 16,
	'md' => 20,
	'lg' => 24,
);
$icon_size  = $icon_sizes[ $size ];

/**
 * Get the label of the active dropdown option.
 *
 * @since 4.0.0
 *
 * @param array $options Array of dropdown options.
 * @return string The label of the active option, or the first option's label if none are active.
 */
$get_active_dropdown_label = function ( $options ) {
	foreach ( $options as $option ) {
		if ( ! empty( $option['active'] ) ) {
			return $option['label'] ?? '';
		}
	}
	return $options[0]['label'] ?? '';
};
?>

<ul class="tutor-nav tutor-nav-<?php echo esc_attr( $size ); ?> tutor-nav-<?php echo esc_attr( $variant ); ?>">
	<?php foreach ( $items as $item ) : ?>
		<?php if ( 'dropdown' === $item['type'] ) : ?>
			<?php
			$options      = $item['options'] ?? array();
			$active_label = $get_active_dropdown_label( $options );
			?>
			<div x-data="tutorPopover({ placement: 'bottom-start', offset: 4 })">
				<button x-ref="trigger" @click="toggle()"
					class="tutor-nav-item<?php echo ! empty( $item['active'] ) ? ' active' : ''; ?>">
					<?php if ( ! empty( $item['icon'] ) ) : ?>
						<?php tutor_utils()->render_svg_icon( $item['icon'], $icon_size, $icon_size ); ?>
					<?php endif; ?>
					<?php echo esc_html( $active_label ); ?>
					<?php
					tutor_utils()->render_svg_icon(
						Icon::CHEVRON_DOWN_2,
						$icon_size,
						$icon_size,
						array( 'class' => 'tutor-icon-subdued' )
					);
					?>
				</button>

				<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()"
					class="tutor-popover tutor-nav-dropdown">
					<?php foreach ( $options as $option ) : ?>
						<a href="<?php echo esc_url( $option['url'] ?? '#' ); ?>"
							class="tutor-nav-dropdown-item <?php echo ! empty( $option['active'] ) ? 'active' : ''; ?>">
							<?php if ( ! empty( $option['icon'] ) ) : ?>
								<?php tutor_utils()->render_svg_icon( $option['icon'], $icon_size, $icon_size ); ?>
							<?php endif; ?>
							<?php echo esc_html( $option['label'] ?? '' ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php else : ?>
			<a href="<?php echo esc_url( $item['url'] ?? '#' ); ?>"
				class="tutor-nav-item<?php echo ! empty( $item['active'] ) ? ' active' : ''; ?>">
				<?php if ( ! empty( $item['icon'] ) ) : ?>
					<?php tutor_utils()->render_svg_icon( $item['icon'], $icon_size, $icon_size ); ?>
				<?php endif; ?>
				<?php echo esc_html( $item['label'] ?? '' ); ?>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
