<?php
/**
 * Navbar Component.
 *
 * @package Tutor\Views
 * @subpackage Tutor\ViewElements
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.5.0
 */

if ( isset( $data ) && count( $data ) ) : ?>
<div class="tutor-wp-dashboard-header tutor-py-16">
	<div class="tutor-admin-container tutor-admin-container-lg">
		<div class="tutor-wp-dashboard-header-inner">
			<div>
				<span class="tutor-wp-dashboard-header-title <?php echo isset( $data['sub_page_title'] ) ? 'tutor-mr-16' : ''; ?>">
					<?php echo esc_html( $data['page_title'] ); ?>
				</span>

				<?php if ( isset( $data['sub_page_title'] ) ) : ?>
					<span class="tutor-mx-8" area-hidden="true">/</span>
					<span class="tutor-fs-7 tutor-color-muted">
						<?php echo esc_html( $data['sub_page_title'] ); ?>
					</span>
				<?php endif; ?>
			</div>

			<?php if ( ! isset( $data['hide_action_buttons'] ) || false === $data['hide_action_buttons'] ) : ?>
			<div class="tutor-d-flex tutor-align-center tutor-gap-1">
				<?php
				// If modal target set then button will be set as modal button otherwise url button.
				$button_class = isset( $data['button_class'] ) ? $data['button_class'] : '';
				if ( ! empty( $data['modal_target'] ) ) :
					?>
					<?php if ( isset( $data['add_button'] ) && $data['add_button'] ) : ?>
						<button class="tutor-btn tutor-btn-primary tutor-d-flex tutor-align-center tutor-gap-1 <?php echo esc_attr( $button_class ); ?>" data-tutor-modal-target="<?php echo esc_html( $data['modal_target'] ); ?>">
							<i class="tutor-icon-plus-light"></i>
							<span><?php echo esc_html( $data['button_title'] ); ?></span>
						</button>
					<?php endif; ?>
				<?php else : ?>
					<?php if ( isset( $data['add_button'] ) && $data['add_button'] ) : ?>
						<?php do_action( 'tutor_data_list_navbar_button' ); ?>
						<a href="<?php echo esc_url( $data['button_url'] ); ?>" class="tutor-btn tutor-btn-primary tutor-d-flex tutor-align-center tutor-gap-1 <?php echo esc_attr( $button_class ); ?>">
							<i class="tutor-icon-plus-light"></i>
							<span><?php echo esc_html( $data['button_title'] ); ?></span>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php endif; ?>
