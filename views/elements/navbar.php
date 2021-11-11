<?php
/**
 * Navbar Component.
 *
 * @package Navbar component
 */

if ( isset( $data ) && count( $data ) ) : ?>
<header
  class="tutor-wp-dashboard-header d-flex justify-content-between align-items-center tutor-px-30 tutor-mb-22" style="margin-left:-20px">
  <div class="header-title text-medium-h5 color-text-primary">
		<span class="text-primary-h5">
			<?php echo esc_html( $data['page_title'] ); ?>
		</span>
		<?php if ( isset( $data['add_button'] ) && $data['add_button'] ) : ?>
			<a class="tutor-pl-10" href="<?php echo esc_url( $data['button_url'] ); ?>">
				<button class="tutor-btn tutor-btn-icon tutor-btn-wordpress-outline tutor-no-hover tutor-btn-sm">
					<span class="btn-icon ttr-plus-bold-filled"></span>
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
					<a href="<?php echo esc_attr( $v['url'] ); ?>" class="filter-btn <?php echo esc_attr( $data['active'] == $v['key'] ? 'is-active' : '' ); ?>">
						<?php echo esc_html( $v['title'] ); ?>
						(<?php echo esc_attr( $v['value'] ); ?>)
					</a>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	<?php if ( isset( $data['grade_button'] ) && $data['grade_button'] ) : ?>
		<ul style="display: flex; column-gap: 15px;">
			<li>
				<a href="<?php echo esc_url( $data['grade_button_url'] ); ?>"><?php echo esc_html( $data['grade_button_title'] ); ?></a>
			</li>
		</ul>
	<?php endif; ?>
  </div>
</header>
<?php endif; ?>
