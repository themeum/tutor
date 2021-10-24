<?php

/**
 * Options generator
 *
 * @param object $this
 */

?>
<!-- .tutor-backend-wrap -->
<section class="tutor-backend-settings-page">
	<header class="tutor-option-header px-3 py-2">
		<div class="title"><?php _e( 'Tools', 'tutor' ); ?></div>
		<div class="search-field">
			<div class="tutor-input-group tutor-form-control-has-icon">
				<span class="tutor-input-group-icon ttr-search-filled"></span>
				<input type="search" autocomplete="off" class="tutor-form-control" placeholder="<?php _e( 'Search', 'tutor' ); ?>" />
			</div>
		</div>
	</header>
	<div class="tutor-option-body">
		<div class="tutor-option-form py-4 px-3">
			<div class="tutor-option-tabs">
				<ul class="tutor-option-nav" data-page="<?php esc_attr_e( $_GET['page'] ); ?>">
					<?php
						foreach ( $tools_fields as $key => $section ) {
							$icon = tutor()->icon_dir . $key . '.svg';
							$active_class = $active_tab == $key ? esc_attr( ' active' ) : '';
							?>
							<li class="tutor-option-nav-item">
								<a data-page="<?php esc_attr_e( $_GET['page'] ); ?>" data-tab="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $active_class ); ?>">
									<img src="<?php echo esc_attr( $icon ); ?>" alt="<?php echo esc_attr( $key ); ?>-icon" />
									<span class="nav-label"><?php echo esc_html( $section['label'] ); ?></span>
								</a>
							</li>
							<?php
						}
					?>
				</ul>
				<!-- end /.tutor-option-nav -->
			</div>

			<!-- end /.tutor-option-tabs -->
			<div class="tutor-option-tab-pages">
				<?php
					foreach ( $tools_fields as $key => $section ) {
						$active_class = $active_tab == $key ? esc_attr( ' active' ) : '';
						?>
						<div id="<?php echo esc_attr( $key ); ?>" class="tutor-option-nav-page<?php echo esc_attr( $active_class ); ?>">
							<?php echo $this->template( $section ); ?>
						</div>
						<?php
					}
				?>
			</div>
		<!-- end /.tutor-option-tab-pages -->
		</div>
	</div>
	<div class="tutor-notification tutor-is-success">
		<div class="tutor-notification-icon">
			<i class="fas fa-check"></i>
		</div>
		<div class="tutor-notification-content">
			<h5>Successful</h5>
			<p>Your file was uploaded</p>
		</div>
		<button class="tutor-notification-close">
			<i class="fas fa-times"></i>
		</button>
	</div>
</section>

<style>
	.isHighlighted {}

	.tutor-notification {
		position: fixed;
		bottom: 40px;
		z-index: 999;
		opacity: 0;
		visibility: hidden;
	}

	.tutor-notification.show {
		opacity: 1;
		visibility: visible;
	}

	.tutor-notification .tutor-notification-close{
		transition: unset;
	}
</style>
