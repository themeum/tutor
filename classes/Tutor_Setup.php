<?php
/**
 * Tutor setup class
 *
 * @package Tutor\Setup
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

use Tutor\Ecommerce\Settings;
use Tutor\Helpers\HttpHelper;
use Tutor\Traits\JsonResponse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage setup functionalities
 *
 * @since 1.0.0
 */
class Tutor_Setup {

	use JsonResponse;

	/**
	 * Register hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus' ) );
		add_action( 'admin_init', array( $this, 'initialize_tutor_onboarding' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_setup_action', array( $this, 'tutor_setup_action' ) );
		add_action( 'wp_ajax_tutor_onboard_setup', array( $this, 'tutor_onboard_setup' ) );
	}

	/**
	 * Setup action
	 *
	 * @since 1.0.0
	 *
	 * @return void send wp_json response
	 */
	public function tutor_setup_action() {
		tutor_utils()->checking_nonce();
		if ( 'setup_action' !== Input::post( 'action', '' ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( tutor_utils()->error_message() );
			return;
		}

		// General Settings.
		$options     = (array) maybe_unserialize( get_option( 'tutor_option' ) );
		$change_data = apply_filters( 'tutor_wizard_attributes', array() );
		foreach ( $change_data as $key => $value ) {
			$post_key = Input::post( $key, '' );
			if ( Input::has( $key ) ) {
				if ( $post_key != $change_data[ $key ] ) {
					if ( '' === $post_key ) {
						unset( $options[ $key ] );
					} else {
						$options[ $key ] = $post_key;
					}
				}
				$options_preset[ $key ] = $post_key;
			} else {
				unset( $options[ $key ] );
			}
		}

		// Payment Settings.
		$withdrawal_payments_methods         = array( 'bank_transfer_withdraw', 'echeck_withdraw', 'paypal_withdraw' );
		$options['tutor_withdrawal_methods'] = array();

		foreach ( $withdrawal_payments_methods as $key ) {
			if ( 'on' === Input::post( $key ) ) {
				$options['tutor_withdrawal_methods'][ $key ] = $key;
			}
		}

		update_option( 'tutor_default_option', $options_preset );
		update_option( 'tutor_option', $options );

		do_action( 'tutor_setup_finished' );

		wp_send_json_success( __( 'Success', 'tutor' ) );
	}

	/**
	 * Add dashboard page without title
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'tutor-setup', '' );
	}

	/**
	 * Initialize tutor onboarding
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function initialize_tutor_onboarding() {
		$setup_page = Input::get( 'page', '' );
		if ( 'tutor-setup' === $setup_page ) {
			$this->tutor_setup_wizard_header();
			$this->tutor_onboard_page();
			$this->initialize_tutor_onboarding_footer();
			exit;
		}
	}

	/**
	 * Tutor Onboarding Setup description
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function tutor_onboard_setup() {
		try {
			tutor_utils()->checking_nonce();
			if ( 'tutor_onboard_setup' !== Input::post( 'action', '' ) || ! current_user_can( 'manage_options' ) ) {
				$this->json_response(
					__( 'Unauthorized', 'tutor' ),
					null,
					HttpHelper::STATUS_FORBIDDEN
				);
			}

			$options                  = (array) maybe_unserialize( get_option( 'tutor_option' ) );
			$options['default_theme'] = Input::post( 'default_theme', '', Input::TYPE_STRING );
			$options['learning_mode'] = Input::post( 'learning_mode', '', Input::TYPE_STRING );

			update_option( 'tutor_option', $options );

			$this->json_response(
				__( 'Onboarding Success', 'tutor' ),
				null,
				HttpHelper::STATUS_OK
			);
		} catch ( \Exception $e ) {
			$this->json_response(
				__( 'Onboarding Failed, Try again!', 'tutor' ),
				null,
				HttpHelper::STATUS_INTERNAL_SERVER_ERROR
			);
		}
	}
	/**
	 * Tutor setup page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_onboard_page() {
		$current_user            = wp_get_current_user();
		$display_name            = $current_user instanceof \WP_User && $current_user->exists() ? tutor_utils()->display_name( $current_user->ID ) : __( 'there', 'tutor' );
		$logo_url                = tutor()->url . 'assets/images/tutor-logo.png';
		$welcome_placeholder_url = 'https://placehold.co/356x176/png?text=Placeholder+Image';
		$card_placeholder_url    = 'https://placehold.co/72x48/png?text=Image';
		?>
		<div id="tutor-onboard-wrapper" class="tutor-d-flex tutor-flex-column tutor-align-center tutor-justify-center">
			<section class="tutor-onboard-screen tutor-onboard-screen-welcome is-active" data-screen="welcome">
				<div class="tutor-onboard-screen-logo tutor-d-flex tutor-justify-center">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Tutor LMS', 'tutor' ); ?>">
				</div>

				<div class="tutor-onboard-card">
					<div class="tutor-onboard-card-body">
						<div class="tutor-onboard-welcome-text">
							<div class="tutor-onboard-welcome-greeting">
								Hello <span> <?php echo esc_html( $display_name ); ?></span>
							</div>
							<h2 class="tutor-onboard-welcome-title">
								<?php esc_html_e( 'Welcome to', 'tutor' ); ?>
								<span><?php esc_html_e( 'Tutor LMS', 'tutor' ); ?></span>
							</h2>
						</div>

						<div class="tutor-onboard-welcome-media">
							<img src="<?php echo esc_url( tutor()->url . 'assets/images/tutor-onboard-hero-img.png' ); ?>" alt="<?php esc_attr_e( 'Setup welcome preview', 'tutor' ); ?>">
						</div>

						<div class="tutor-onboard-welcome-description">
							<?php
								echo wp_kses(
									sprintf(
										/* translators: %s: Number of trusted websites */
										__( 'Get started with an all-in-one platform to create, manage, and sell your courses effortlessly, trusted by over %s eLearning websites worldwide.', 'tutor' ),
										'<span>100k+</span>'
									),
									array(
										'span' => array(),
									)
								);
							?>
						</div>
					</div>

					<div class="tutor-onboard-card-footer">
						<button type="button" class="tutor-btn tutor-btn-primary tutor-btn-block tutor-onboard-next-screen" data-target="preferences">
							<span><?php esc_html_e( 'Next', 'tutor' ); ?></span>
							<span aria-hidden="true">&#8594;</span>
						</button>
					</div>
				</div>
			</section>

			<section class="tutor-onboard-screen tutor-onboard-screen-preferences" data-screen="preferences">
				<div class="tutor-onboard-screen-logo tutor-d-flex tutor-justify-center">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Tutor LMS', 'tutor' ); ?>">
				</div>

				<div class="tutor-onboard-card">
					<form class="tutor-onboard-setup-form" method="post">
						<input type="hidden" name="action" value="tutor_onboard_setup">

						<div class="tutor-onboard-card-body">
							<h2 class="tutor-onboard-preferences-title"><?php esc_html_e( 'Choose a look for your learners', 'tutor' ); ?></h2>

							<div class="tutor-onboard-preferences-group">
								<h3 class="tutor-onboard-preferences-label"><?php esc_html_e( 'Default Learning Mode', 'tutor' ); ?></h3>

								<div class="tutor-onboard-choice-wrapper tutor-onboard-choice-learning">
									<label class="tutor-onboard-choice-card is-selected">
										<input type="radio" class="tutor-onboard-choice-input" name="learning_mode" value="modern" checked>
										<span class="tutor-onboard-choice-media">
											<img src="<?php echo esc_url( tutor()->assets_url . 'images/images-v2/learning-mode/modern.svg' ); ?>" alt="<?php esc_attr_e( 'Modern mode preview', 'tutor' ); ?>">
										</span>
										<span class="tutor-onboard-choice-text"><?php esc_html_e( 'Modern', 'tutor' ); ?></span>
									</label>

									<label class="tutor-onboard-choice-card">
										<input type="radio" class="tutor-onboard-choice-input" name="learning_mode" value="kids">
										<span class="tutor-onboard-choice-media">
											<img src="<?php echo esc_url( tutor()->assets_url . 'images/images-v2/learning-mode/kids.svg' ); ?>" alt="<?php esc_attr_e( 'Kids mode preview', 'tutor' ); ?>">
										</span>
										<span class="tutor-onboard-choice-text"><?php esc_html_e( 'Kids', 'tutor' ); ?></span>
									</label>
								</div>
							</div>

							<div class="tutor-onboard-preferences-group">
								<h3 class="tutor-onboard-preferences-label"><?php esc_html_e( 'Default Theme', 'tutor' ); ?></h3>

								<div class="tutor-onboard-choice-wrapper tutor-onboard-choice-theme">
									<label class="tutor-onboard-choice-card is-selected">
										<input type="radio" class="tutor-onboard-choice-input" name="default_theme" value="light" checked>
										<span class="tutor-onboard-choice-media">
											<img src="<?php echo esc_url( tutor()->assets_url . 'images/images-v2/default-theme/light.webp' ); ?>" alt="<?php esc_attr_e( 'Light theme preview', 'tutor' ); ?>">
										</span>
										<span class="tutor-onboard-choice-text"><?php esc_html_e( 'Light', 'tutor' ); ?></span>
									</label>

									<label class="tutor-onboard-choice-card">
										<input type="radio" class="tutor-onboard-choice-input" name="default_theme" value="dark">
										<span class="tutor-onboard-choice-media">
											<img src="<?php echo esc_url( tutor()->assets_url . 'images/images-v2/default-theme/dark.webp' ); ?>" alt="<?php esc_attr_e( 'Dark theme preview', 'tutor' ); ?>">
										</span>
										<span class="tutor-onboard-choice-text"><?php esc_html_e( 'Dark', 'tutor' ); ?></span>
									</label>

									<label class="tutor-onboard-choice-card">
										<input type="radio" class="tutor-onboard-choice-input" name="default_theme" value="system">
										<span class="tutor-onboard-choice-media">
											<img src="<?php echo esc_url( tutor()->assets_url . 'images/images-v2/default-theme/auto.webp' ); ?>" alt="<?php esc_attr_e( 'Auto theme preview', 'tutor' ); ?>">
										</span>
										<span class="tutor-onboard-choice-text"><?php esc_html_e( 'Auto', 'tutor' ); ?></span>
									</label>
								</div>
							</div>

							<div class="tutor-onboard-load-sample tutor-form-check tutor-d-flex tutor-align-center tutor-gap-1 tutor-onboard-checkbox">
								<input id="tutor-onboard-load-sample-course" type="checkbox" name="tutor_onboard_load_sample_course" value="1" class="tutor-form-check-input">
								<label for="tutor-onboard-load-sample-course" class="tutor-onboard-checkbox-label">
									<?php esc_html_e( 'Load sample courses to help you get started.', 'tutor' ); ?>
								</label>
							</div>
						</div>

						<div class="tutor-onboard-card-footer tutor-onboard-card-footer-stack">
							<button type="submit" class="tutor-onboard-submit-btn tutor-btn tutor-btn-primary tutor-btn-block" data-screen="loading">
								<span><?php esc_html_e( 'Let\'s go', 'tutor' ); ?></span>
								<span aria-hidden="true">&#8594;</span>
							</button>
							<p class="tutor-onboard-help-text"><?php esc_html_e( 'Don\'t worry, you can always change these settings later! 😊', 'tutor' ); ?></p>
						</div>
					</form>
				</div>
			</section>

			<section class="tutor-onboard-screen tutor-onboard-screen-loading" data-screen="loading">
				<div class="tutor-onboard-card-loading">
					<?php $loading_text = __( 'The world is changing with AI, but the need for great teachers never will.', 'tutor' ); ?>
					<span class="tutor-onboard-loading-text" data-text="<?php echo esc_attr( $loading_text ); ?>">
						<?php echo esc_html( $loading_text ); ?>
					</span>
				</div>
			</section>
		</div>
		<?php
	}

	/**
	 * Initialize tutor onboarding header
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function tutor_setup_wizard_header() {
		set_current_screen();
		?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php esc_html_e( 'Tutor &rsaquo; Setup Wizard', 'tutor' ); ?></title>
			<?php
			try {
				do_action( 'admin_enqueue_scripts' );
			} catch ( \Throwable $th ) { //phpcs:ignore
			}
			?>
			<?php wp_print_scripts( 'tutor-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
			</head>
			<body class="tutor-setup wp-core-ui">
			<?php
	}

	/**
	 * Initialize tutor onboarding footer
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function initialize_tutor_onboarding_footer() {
		?>
				</body>
			</html>
			<?php
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$page = Input::get( 'page', '' );
		if ( 'tutor-setup' === $page ) {
			wp_enqueue_style( 'tutor-setup', tutor()->url . 'assets/css/tutor-setup.min.css', array(), TUTOR_VERSION );
			wp_register_script( 'tutor-setup', tutor()->url . 'assets/js/tutor-setup.js', array( 'wp-i18n' ), TUTOR_VERSION, true );
			wp_localize_script(
				'tutor-setup',
				'_tutorobject',
				array(
					'ajaxurl'         => admin_url( 'admin-ajax.php' ),
					'tutor_dashboard' => admin_url( 'admin.php?page=tutor' ),
					'nonce_key'       => tutor()->nonce,
					tutor()->nonce    => wp_create_nonce( tutor()->nonce_action ),
				)
			);
			wp_set_script_translations( 'tutor-setup', 'tutor', tutor()->path . 'languages/' );
		}
	}


	/**
	 * Check if welcome page already visited
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_welcome_page_visited(): bool {
		return false;
	}

	/**
	 * Mark as welcome page visited
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function mark_as_visited() {
		update_option( 'tutor_welcome_page_visited', true );
	}
}