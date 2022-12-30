<?php
/**
 * Template for settings page
 *
 * @package Tutor\Views
 * @subpackage Tutor\Options
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

use TUTOR\Input;

?>
<div class="tutor-admin-wrap">
	<div class="tutor-admin-header is-sticky">
		<div class="tutor-row tutor-align-center">
			<div class="tutor-col-md-3 tutor-col-lg-4 tutor-mb-16 tutor-mb-md-0">
				<span class="tutor-fs-5 tutor-fw-medium tutor-mr-16"><?php esc_html_e( 'Settings', 'tutor' ); ?></span>
			</div>
			<div class="tutor-col-md-5 tutor-col-xl-6 tutor-mb-24 tutor-mb-md-0">
				<div class="tutor-options-search tutor-form-wrap">
					<span class="tutor-icon-search tutor-form-icon" area-hidden="true"></span>
					<input type="search" accesskey="s" autofocus autocomplete="off" id="search_settings" class="tutor-form-control tutor-form-control-lg" placeholder="<?php esc_html_e( 'Search ...⌃⌥ + S or Alt+S for shortcut', 'tutor' ); ?>" />
					<div class="search-popup-opener search_result"></div>
				</div>
			</div>
			<div class="tutor-col-md-4 tutor-col-lg-3 tutor-col-xl-2 tutor-d-flex tutor-justify-end">
				<div>
					<button id="save_tutor_option" class="tutor-btn tutor-btn-primary" disabled form="tutor-option-form">
						<?php esc_html_e( 'Save Changes', 'tutor' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-admin-container">
		<form class="tutor-option-form" id="tutor-option-form">
		<input type="hidden" name="action" value="tutor_option_save">
		<div class="tutor-row tutor-gx-lg-0 tutor-my-24">
			<div class="tutor-col-auto tutor-col-lg-2">
				<ul class="tutor-option-tabs tutor-nav tutor-nav-pills tutor-nav-v" tutor-option-tabs>
					<?php
					foreach ( $option_fields as $key => $section ) {
						$active_class = $active_tab == $key ? esc_attr( ' is-active' ) : '';
						if ( $active_tab == $key ) {
							$active_class = ' is-active';
						} elseif ( 'general' === $key && ! in_array( $active_tab, array_keys( $option_fields ) ) ) {
							$active_class = ' is-active';
						}
						$get_page = Input::get( 'page', '' );
						?>
						<li class="tutor-nav-item">
							<a class="tutor-nav-link<?php echo esc_attr( $active_class ); ?>" data-page="<?php echo esc_attr( $get_page ); ?>" data-tab="<?php echo esc_attr( $key ); ?>">
								<span class="<?php echo esc_attr( $section['icon'] ); ?>" area-hidden="true"></span>
								<span class="tutor-ml-12 tutor-d-none tutor-d-lg-block" tutor-option-label><?php echo esc_html( $section['label'] ); ?></span>
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
					foreach ( $option_fields as $key => $section ) {
						$active_class = $active_tab == $key ? esc_attr( ' is-active' ) : '';

						if ( $active_tab == $key ) {
							$active_class = ' is-active';
						} elseif ( 'general' === $key && ! in_array( $active_tab, array_keys( $option_fields ) ) ) {
							$active_class = ' is-active';
						}
						?>
						<div id="<?php echo esc_attr( $key ); ?>" class="tutor-option-nav-page<?php echo esc_attr( $active_class ); ?>">
							<?php
							if ( is_array( $section ) ) {
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
		</form>
	</div>
	<?php
	//phpcs:ignore -- contain safe data
	echo $this->view_template( 'common/modal-confirm.php', array() );
	?>
</div>
