<?php

/**
 * Options generator
 *
 * @param object $this
 */

?>
<!-- .tutor-backend-wrap -->
<section class="tutor-backend-settings-page">
	<header class="tutor-option-header tutor-bs-px-3 tutor-bs-py-2">
		<div class="title"><?php _e( 'Tools', 'tutor' ); ?></div>
		<div class="search-field">
			<div class="tutor-input-group tutor-form-control-has-icon">
				<span class="tutor-input-group-icon ttr-search-filled"></span>
				<input type="search" autocomplete="off" class="tutor-form-control" placeholder="<?php _e( 'Search', 'tutor' ); ?>" />
			</div>
		</div>
	</header>
	<div class="tutor-option-body">
		<div class="tutor-option-form tutor-bs-py-4 tutor-bs-px-3">
			<div class="tutor-option-tabs">

				<ul class="tutor-option-nav" data-page="<?php esc_attr_e( $_GET['page'] ); ?>">
					<?php
					foreach ( $tools_fields as $key => $section ) {
						$icon         = tutor()->icon_dir . $key . '.svg';
						$active_class = $active_tab == $key ? esc_attr( ' active' ) : '';
						$page_url     = add_query_arg( 'sub_page', $section['slug'], admin_url( 'admin.php?page=tutor-tools' ) );
						?>
							<li class="tutor-option-nav-item">
								<a href="<?php echo esc_url( $page_url ); ?>" class="<?php echo esc_attr( $active_class ); ?>">
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
						<?php
						if ( isset( $section['template'] ) && ! empty( $section['template'] ) ) {
							echo $this->template( $section );
						}
						?>
						</div>
					<?php
				}
				?>
			</div>
		<!-- end /.tutor-option-tab-pages -->
		</div>
	</div>
</section>
