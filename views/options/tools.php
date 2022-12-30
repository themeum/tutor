<?php
/**
 * Options generator
 *
 * @package Tutor\Views
 * @subpackage Tutor\Options
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>
<div class="tutor-admin-wrap">
	<div class="tutor-wp-dashboard-header tutor-px-24 tutor-mb-24">
		<div class="tutor-d-lg-flex tutor-align-lg-center tutor-px-12 tutor-py-16">
			<span class="tutor-fs-5 tutor-fw-medium tutor-mr-16"><?php esc_html_e( 'Tools', 'tutor' ); ?></span>
		</div>
	</div>

	<div class="tutor-admin-container">
		<div class="tutor-row tutor-gx-lg-0 tutor-my-24">
			<div class="tutor-col-auto tutor-col-lg-2">
				<ul class="tutor-option-tabs tutor-nav tutor-nav-pills tutor-nav-v" data-page="<?php echo esc_attr( wp_unslash( $_GET['page'] ) ); ?>">
					<?php
					foreach ( $tools_fields as $key => $section ) {
						$icon         = tutor()->icon_dir . $key . '.svg';
						$active_class = $active_tab == $key ? esc_attr( ' is-active' ) : '';
						$page_url     = add_query_arg( 'sub_page', esc_attr( $section['slug'] ), admin_url( 'admin.php?page=tutor-tools' ) );
						?>
							<li class="tutor-nav-item">
								<a class="tutor-nav-link<?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $page_url ); ?>">
									<span class="<?php echo esc_attr( $section['icon'] ); ?>" area-hidden="true"></span>
									<span class="tutor-ml-12 tutor-d-none tutor-d-lg-block"><?php echo esc_html( $section['label'] ); ?></span>
								</a>
							</li>
						<?php
					}
					?>
				</ul>
			</div>

			<div class="tutor-col-1 tutor-text-center tutor-d-none tutor-d-lg-block">
				<div class="tutor-vr tutor-mx-32 tutor-d-inline-block"></div>
			</div>

			<div class="tutor-col-10 tutor-col-lg-9">
				<div class="tutor-option-tab-pages">
					<?php
					foreach ( $tools_fields as $key => $section ) {
						$active_class = $active_tab == $key ? esc_attr( ' is-active' ) : '';
						?>
							<div id="<?php echo esc_attr( $key ); ?>" class="tutor-option-nav-page<?php echo esc_attr( $active_class ); ?>">
							<?php
							if ( isset( $section['template'] ) && ! empty( $section['template'] ) ) {
								echo $this->template( $section ); //phpcs:ignore -- contain safe data
							}
							?>
							</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>

	<?php
	//phpcs:ignore -- contain safe data
	echo $this->view_template( 'common/modal-confirm.php', array() ); ?>
</div>
