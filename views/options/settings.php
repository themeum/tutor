<?php
/**
 * Template for editing email template
 *
 * @since v.2.0
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Certificate
 * @version 2.0
 */

?>
<!-- .tutor-backend-wrap -->
<section class="tutor-backend-settings-page" style="margin-left: -20px;">
	<header class="tutor-option-header tutor-bs-px-3 tutor-bs-py-2">
		<div class="title"><?php esc_html_e( 'Settings', 'tutor' ); ?></div>
		<div class="search-field">
			<div class="tutor-input-group tutor-form-control-has-icon tutor-form-control-sm">
				<span class="ttr-search-filled tutor-input-group-icon color-black-50"></span>
				<input type="search" autofocus autocomplete="off" id="search_settings" class="tutor-form-control" placeholder="<?php esc_html_e( 'Search ... ', 'tutor' ); ?>" />
				<div class="search-popup-opener search_result">
					<a href="#">
						<div class="search_result_title">
							<i class="ttr-search-filled"></i>›
							<span>Result results one</span>
						</div>
						<div class="search_navigation">
							<span>General</span>
							<i class="las la-angle-right"></i>
							<span>Instructor</span>
						</div>
					</a>
					<a href="#">
						<div class="search_result_title">
							<i class="ttr-search-filled"></i>
							<span>Result results tow</span>
						</div>
						<div class="search_navigation">
							<span>Design</span>
							<i class="las la-angle-right"></i>
							<span>Instructor</span>
						</div>
					</a>
					<a href="#">
						<div class="search_result_title">
							<i class="ttr-search-filled"></i>
							<span>Result results three</span>
						</div>
						<div class="search_navigation">
							<span>General</span>
							<i class="las la-angle-right"></i>
							<span>Instructor</span>
						</div>
					</a>
				</div>
			</div>
			<div class="tutor-input-group tutor-form-control-has-icon">
				<!-- <span class="tutor-input-group-icon ttr-search-filled"></span>
				<input type="search" autofocus autocomplete="off" id="search_settings" class="tutor-form-control" placeholder="<?php esc_html_e( 'Search', 'tutor' ); ?>" /> -->
				
			</div>
		</div>
		<div class="save-button">
			<button id="save_tutor_option" class="tutor-btn"><?php _e( 'Save Changes', 'tutor' ); ?></button>
		</div>
	</header>
	<div class="tutor-option-body">
		<form class="tutor-option-form tutor-bs-py-4 tutor-bs-px-3" id="tutor-option-form">
			<input type="hidden" name="action" value="tutor_option_save">
			<div class="tutor-option-tabs">
				<ul class="tutor-option-nav tutor-bs-mt-0">
					<?php
					foreach ( $option_fields as $key => $section ) {
						$icon         = tutor()->icon_dir . $key . '.svg';
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
			</div>
			<!-- end /.tutor-option-tabs -->

			<div class="tutor-option-tab-pages">
				<?php
				foreach ( $option_fields as $key => $section ) {
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
		</form>
	</div>
</section>

<style>
	.color-picker-input input[type=color]:focus,.color-picker-input input[type=color]:active {box-shadow: none;}
	.color-preset-input [type="radio"]{position: absolute;opacity: 0;visibility: hidden;z-index: -1;}
</style>
