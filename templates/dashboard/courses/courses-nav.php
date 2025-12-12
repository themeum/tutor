<?php
/**
 * Courses nav template
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

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
		'count' => $options[0]['count'],
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
<!-- Courses nav  -->
<div class="tutor-dashboard-page-nav tutor-p-6">
	<ul class="tutor-dashboard-page-nav-list">
		<?php foreach ( $course_tab as $item ) : ?>
			<li class="tutor-dashboard-page-nav-item">
				<?php if ( 'dropdown' === ( $item['type'] ?? 'link' ) ) : ?>
					<?php
					$options     = $item['options'] ?? array();
					$active_info = get_active_dropdown_label( $options );
					?>
					<div x-data="tutorPopover({ placement: 'bottom-start', offset: 4 })">
						<button x-ref="trigger" @click="toggle()"
							class="tutor-dashboard-page-nav-link <?php echo ! empty( $item['active'] ) ? 'active' : ''; ?>">
							<?php if ( ! empty( $item['icon'] ) ) : ?>
								<?php tutor_utils()->render_svg_icon( $item['icon'], 20, 20 ); ?>
							<?php endif; ?>
							<?php echo esc_html( $active_info['label'] ); ?>
							<?php if ( ! empty( $active_info['count'] ) ) : ?>
								<span class="tutor-ml-1">
									(<?php echo esc_html( $active_info['count'] ); ?>)
								</span>
							<?php endif; ?>
							<?php
							tutor_utils()->render_svg_icon(
								Icon::CHEVRON_DOWN,
								20,
								20,
								array( 'class' => 'tutor-icon-subdued' )
							);
							?>
						</button>

						<div x-ref="content" x-show="open" x-cloak @click.outside="handleClickOutside()"
							class="tutor-popover tutor-dashboard-page-nav-dropdown">
							<ul>
								<?php foreach ( $options as $option ) : ?>
									<li>
										<a href="<?php echo esc_url( $option['url'] ?? '#' ); ?>"
											class="tutor-dashboard-page-nav-dropdown-link <?php echo ! empty( $option['active'] ) ? 'active' : ''; ?>">
											<?php if ( ! empty( $option['icon'] ) ) : ?>
												<?php tutor_utils()->render_svg_icon( $option['icon'], 20, 20 ); ?>
											<?php endif; ?>
											<?php echo esc_html( $option['label'] ?? '' ); ?>
											<?php if ( ! empty( $option['count'] ) ) : ?>
												<span class="ml-1">
													(<?php echo esc_html( $option['count'] ); ?>)
												</span>
											<?php endif; ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				<?php else : ?>
					<a href="<?php echo esc_url( $item['url'] ?? '#' ); ?>" 
						class="tutor-dashboard-page-nav-link <?php echo ! empty( $item['active'] ) ? 'active' : ''; ?>">
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<?php tutor_utils()->render_svg_icon( $item['icon'], 20, 20 ); ?>
						<?php endif; ?>
						<?php echo esc_html( $item['label'] ?? '' ); ?>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>