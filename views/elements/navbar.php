<?php
/**
 * Navbar Component.
 *
 * @package Navbar component
 */

if ( isset( $data ) && count( $data ) ) : ?>
	<header class="tutor-wp-dashboard-header tutor-d-xl-flex tutor-tutor-justify-content-between tutor-align-items-center tutor-px-30 tutor-py-14 tutor-mb-26" style="margin-left:-20px">
		<div class="header-title-wrap tutor-d-flex tutor-align-items-center tutor-flex-wrap tutor-mb-xl-0 tutor-mb-4 header-title tutor-text-medium-h5 tutor-color-text-primary">
			<span class="text-primary-h5">
				<?php echo esc_html( $data['page_title'] ); ?>
			</span>
			<!--modal button or url button -->
			<?php
			// If modal target set then button will be set as modal button otherwise url button.
			if ( isset( $data['modal_target'] ) && '' !== $data['modal_target'] ) :
				?>
				<?php if ( isset( $data['add_button'] ) && $data['add_button'] ) : ?>
					<button  data-tutor-modal-target="<?php echo esc_html( $data['modal_target'] ); ?>"  class="tutor-btn tutor-btn-wordpress-outline tutor-btn-sm">
						<span class="tutor-icon-20 tutor-icon-plus-bold-filled"></span>
						<span><?php echo esc_html( $data['button_title'] ); ?></span>
					</button>
				<?php endif; ?>
			<?php else : ?>
			<?php if ( isset( $data['add_button'] ) && $data['add_button'] ) : ?>
				<a class="tutor-pl-10" href="<?php echo esc_url( $data['button_url'] ); ?>">
					<button class="tutor-btn tutor-btn-wordpress-outline tutor-btn-sm">
						<span class="tutor-icon-20 tutor-icon-plus-bold-filled"></span>
						<span><?php echo esc_html( $data['button_title'] ); ?></span>
					</button>
				</a>
			<?php endif; ?>
			<?php endif; ?>
		</div>
		<div class="filter-btns tutor-text-regular-body tutor-color-text-subsued">
			<?php if ( isset( $data['tabs'] ) ) : ?>
				<div class="tutor-admin-page-navbar-tabs filter-btns">
					<?php foreach ( $data['tabs'] as $key => $v ) : ?>
						<a href="<?php echo esc_attr( $v['url'] ); ?>" class="filter-btn <?php echo esc_attr( $data['active'] == $v['key'] ? 'is-active' : '' ); ?>">
							<span class="filter-btn-name"><?php echo esc_html( $v['title'] ); ?></span>
							<span class="filter-btn-number"><?php if ( isset( $v['value'] ) ) : ?>
								(<?php echo esc_attr( $v['value'] ); ?>)
							<?php endif; ?></span>
						</a>
					<?php endforeach; ?>
					<!-- <ul style="display: flex; column-gap: 15px;"></ul> -->
				</div>
			<?php endif; ?>
		</div>
	</header>
<?php endif; ?>
