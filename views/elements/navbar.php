<?php
/**
 * Navbar Component.
 *
 * @package Navbar component
 */

if ( isset( $data ) && count( $data ) ) : ?>
<style>
	.tutor-admin-page-navbar-tabs li a {
		color: #000;
	}
	.tutor-admin-page-navbar-tabs li.active a {
		color: #2271b1;
	}
</style>
	<div class="tutor-admin-page-navbar" style="display: flex; justify-content: space-between;">
		<div class="tutor-admin-page-navbar-title">
			<span class="text-primary-h5">
				<?php esc_html_e( $data['page_title'] ); ?>
			</span>
		</div>
		<?php if ( isset( $data['tabs'] ) ) : ?>
		<div class="tutor-admin-page-navbar-tabs">
			<ul style="display: flex; column-gap: 15px;">
				<?php foreach ( $data['tabs'] as $key => $v ) : ?>
					<li class="<?php esc_attr_e( $data['active'] == $v['key'] ? 'active' : '' ); ?>">
						<a href="<?php esc_attr_e( $v['url'] ); ?>">
							<?php esc_html_e( $v['title'] ); ?>
							(<?php esc_attr_e( $v['value'] ); ?>)
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
