<?php
/**
 * Navbar Component.
 *
 * @package Navbar component
 */

if ( isset( $data ) && count( $data ) ) : ?>
<header class="tutor-wp-dashboard-header d-flex justify-content-between align-items-center tutor-px-30 tutor-py-20 tutor-mb-22" style="margin-left:-20px">
  <div class="header-title text-medium-h5 color-text-primary">
		<span class="text-primary-h5">
			<?php echo esc_html( $data['page_title'] ); ?>
		</span>
		<?php if ( isset( $data['add_button'] ) && $data['add_button'] ) : ?>
			<a class="tutor-pl-10" href="<?php echo esc_url( $data['button_url'] ); ?>">
				<button class='tutor-btn tutor-btn-icon tutor-btn-wordpress-outline tutor-btn-sm'>
				<span class="btn-icon ttr-plus-filled"></span>
					<span><?php echo esc_html( $data['button_title'] ); ?></span>
				</button>
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
