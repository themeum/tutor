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
				<?php echo esc_html( $data['page_title'] ); ?>
			</span>
			<?php if ( isset( $data['add_button'] ) && $data['add_button'] ) : ?>
				<a href="<?php echo esc_url( $data['button_url'] ); ?>" class="">
					<?php echo esc_html( $data['button_title'] ); ?>
				</a>
			<?php endif; ?>
		</div>
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
<?php endif; ?>
