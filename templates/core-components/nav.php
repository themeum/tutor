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
 * @return array The label and count of the active option, or the first option's label if none are active.
 */
function get_active_dropdown_label( $options ) {
	$active_info = array(
		'label' => $options[0]['label'],
		'count' => $options[0]['count'] ?? 0,
	);
	foreach ( $options as $option ) {
		if ( ! empty( $option['active'] ) ) {
			$active_info['label'] = $option['label'];
		}
		if ( ! empty( $option['active'] ) && ! empty( $option['count'] ) ) {
			$active_info['count'] = $option['count'];
		}
	}
	return $active_info;
}
?>

<div class="tutor-nav tutor-nav-<?php echo esc_attr( $size ); ?> tutor-nav-<?php echo esc_attr( $variant ); ?>">
	<?php foreach ( $items as $item ) : ?>
		<?php if ( 'dropdown' === $item['type'] ) : ?>
			<?php
			$options      = $item['options'] ?? array();
			$active_info = get_active_dropdown_label( $options );
			?>
			<div x-data="tutorPopover({ placement: 'bottom-start', offset: 4 })">
				<button x-ref="trigger" @click="toggle()"
					class="tutor-nav-item<?php echo ! empty( $item['active'] ) ? ' active' : ''; ?>">
					<?php if ( ! empty( $item['icon'] ) ) : ?>
						<?php tutor_utils()->render_svg_icon( $item['icon'], $icon_size, $icon_size ); ?>
					<?php endif; ?>
					<?php echo esc_html( $active_info['label'] ); ?>
					<?php if ( ! empty( $active_info['count'] ) ) : ?>
						<span class="tutor-ml-1">
							(<?php echo esc_html( $active_info['count'] ); ?>)
						</span>
					<?php endif; ?>
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
							<?php if ( ! empty( $option['count'] ) ) : ?>
								<span class="ml-1">
									(<?php echo esc_html( $option['count'] ); ?>)
								</span>
							<?php endif; ?>
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
</div>
