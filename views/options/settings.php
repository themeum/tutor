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

$url_page = isset($_GET['tab_page']) ? $_GET['tab_page'] : '';
$option_fields = $data['setting_fields'];
?>
<!-- .tutor-backend-wrap -->
<section class="tutor-backend-settings-page tutor-grid" style="padding-top: 60px;">
	<header class="tutor-option-header px-3 py-2" style="position: fixed;right:0;z-index:99;width:calc(100% - 160px);top:32px;">
		<div class="title"><?php esc_html_e( 'Settings', 'tutor' ); ?></div>
		<div class="search-field">
			<div class="tutor-input-group tutor-form-control-has-icon">
				<span class="tutor-input-group-icon tutor-v2-icon-test icon-search-filled"></span>
				<input type="search" autofocus autocomplete="off" id="search_settings" class="tutor-form-control" placeholder="<?php esc_html_e( 'Search', 'tutor' ); ?>" />
				<div class="search-popup-opener search_result">
					<a href="#">
						<div class="search_result_title">
							<i class="las la-search"></i>›
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
							<i class="las la-search"></i>
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
							<i class="las la-search"></i>
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
		</div>
		<div class="save-button">
			<button id="save_tutor_option" class="tutor-btn"><?php _e( 'Save Changes', 'tutor' ); ?></button>
		</div>
	</header>
	<div class="tutor-option-body">
		<form class="tutor-option-form py-4 px-3" id="tutor-option-form">
			<input type="hidden" name="action" value="tutor_option_save">
			<div class="tutor-option-tabs">
				<ul class="tutor-option-nav">
					<?php
					foreach ( $option_fields as $args ) {
						$first_key = array_keys( $args['sections'] )[0];
						foreach ( $args['sections'] as $key => $section ) {
							$icon       = tutor()->icon_dir . $key . '.svg';
							$tab_page   = get_response( 'tab_page' );
							$is_current = ( ! isset( $tab_page ) && esc_attr( $first_key ) === esc_attr( $key ) ) || esc_attr( $tab_page ) === esc_attr( $key ) ? esc_attr( ' active' ) : null;
							?>
						<li class="tutor-option-nav-item">
							<a data-tab="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $is_current ); ?>">
								<img src="<?php echo esc_attr( $icon ); ?>" alt="<?php echo esc_attr( $key ); ?>-icon" />
								<span class="nav-label"><?php echo esc_html( $section['label'] ); ?></span>
							</a>
						</li>
							<?php
						}
					}
					?>
				</ul>
			</div>
			<!-- end /.tutor-option-tabs -->
			<div class="tutor-option-tab-pages">
				<?php
				foreach ( $option_fields as $key => $args ) {
					foreach ( $args['sections'] as $key => $section ) {
						$tab_page   = get_response( 'tab_page' );
						$is_current = ( ! isset( $tab_page ) && esc_attr( $first_key ) === esc_attr( $key ) ) || esc_attr( $tab_page ) === esc_attr( $key ) ? esc_attr( ' active' ) : null;
						?>
						<div id="<?php echo esc_attr( $key ); ?>" class="tutor-option-nav-page<?php echo esc_attr( $is_current ); ?>">
							<?php echo $this->template( $section ); ?>
						</div>
						<?php
					}
				}
				?>

			</div>
			<!-- end /.tutor-option-tab-pages -->
		</form>
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
