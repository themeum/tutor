<?php
/**
 * Student Navbar Component.
 *
 * @package Navbar component
 */

if ( isset( $data ) && count( $data ) ) : ?>
<header
  class="tutor-wp-dashboard-header d-flex justify-content-between align-items-center tutor-px-30 tutor-py-20 tutor-mb-22 tutor-mr-20"
  style="border: 1px solid #f1f1f1">
  <div class="header-title text-medium-h5 color-text-primary mb-lg-0 mb-3">
		<span class="text-primary-h5">
			<?php echo esc_html( $data['page_title'] ); ?>
		</span>
		<?php if ( isset( $data['add_button'] ) && $data['add_button'] ) : ?>
			<a href="<?php echo esc_url( $data['button_url'] ); ?>" class="">
				<?php echo esc_html( $data['button_title'] ); ?>
			</a>
		<?php endif; ?>
  </div>
</header>
<?php endif; ?>
