<?php
/**
 * Navbar Component.
 *
 * @package Navbar component
 */

if ( isset( $data ) && count( $data ) ) : ?>
<header
  class="tutor-wp-dashboard-header d-flex justify-content-between align-items-center tutor-px-30 tutor-py-20 tutor-mb-22 tutor-mr-15"
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
  <div class="filter-btns text-regular-body color-text-subsued">
	<?php if ( isset( $data['tabs'] ) ) : ?>
		<div class="tutor-admin-page-navbar-tabs">
			<ul style="display: flex; column-gap: 15px;">
				<?php foreach ( $data['tabs'] as $key => $v ) : ?>
					<li class="<?php echo esc_attr( $data['active'] == $v['key'] ? 'active' : '' ); ?>">
						<a href="<?php echo esc_attr( $v['url'] ); ?>">
							<?php echo esc_html( $v['title'] ); ?>
							(<?php echo esc_attr( $v['value'] ); ?>)
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
  </div>
</header>
<?php endif; ?>
