<?php
/**
 * Onboarding page
 *
 * @package Tutor
 * @since 4.0.0 onboarding
 */

use Tutor\Components\SvgIcon;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

set_current_screen();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php esc_html_e( 'Tutor &rsaquo; Onboarding', 'tutor' ); ?></title>
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
		$current_user = wp_get_current_user(); //phpcs:ignore
		$display_name = $current_user instanceof \WP_User && $current_user->exists() ? tutor_utils()->display_name( $current_user->ID ) : __( 'there', 'tutor' );
		$logo_url     = tutor()->url . 'assets/images/tutor-logo.png';
	?>
		<div id="tutor-onboard-wrapper" class="tutor-d-flex tutor-flex-column tutor-align-center tutor-justify-center">
			<section class="tutor-onboard-screen tutor-onboard-screen-welcome is-active" data-screen="welcome">
				<div class="tutor-onboard-screen-logo tutor-d-flex tutor-justify-center">
					<img src="<?php echo esc_url( tutor()->url . 'assets/images/tutor-logo.png' ); ?>" alt="<?php esc_attr_e( 'Tutor LMS', 'tutor' ); ?>">
				</div>

				<div class="tutor-onboard-card">
					<div class="tutor-onboard-card-body">
						<div class="tutor-onboard-welcome-text">
							<div class="tutor-onboard-welcome-greeting">
								<?php
								echo wp_kses(
									sprintf(
										/* translators: %1$s: User display name wrapped in a span element. */
										__( 'Hello %1$s', 'tutor' ),
										'<span>' . esc_html( $display_name ) . '</span>'
									),
									array(
										'span' => array(),
									)
								);
								?>
							</div>
							<h2 class="tutor-onboard-welcome-title">
								<?php
								echo wp_kses(
									sprintf(
										/* translators: %1$s: Tutor LMS text wrapped in a span element. */
										__( 'Welcome to %1$s', 'tutor' ),
										'<span>' . esc_html__( 'Tutor LMS', 'tutor' ) . '</span>'
									),
									array(
										'span' => array(),
									)
								);
								?>
							</h2>
						</div>

						<div class="tutor-onboard-welcome-media">
							<img src="<?php echo esc_url( tutor()->url . 'assets/images/tutor-onboard-hero-img.webp' ); ?>" alt="<?php esc_attr_e( 'Setup welcome preview', 'tutor' ); ?>">
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
							<?php SvgIcon::make()->name( Icon::ARROW_RIGHT_2 )->flip_rtl()->render(); ?>
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
								<input id="tutor-onboard-load-sample-course" type="checkbox" name="tutor_onboard_load_sample_course" value="1" class="tutor-form-check-input" checked>
								<label for="tutor-onboard-load-sample-course" class="tutor-onboard-checkbox-label">
									<?php esc_html_e( 'Load sample courses to help you get started.', 'tutor' ); ?>
								</label>
							</div>
						</div>

						<div class="tutor-onboard-card-footer tutor-onboard-card-footer-stack">
							<button type="submit" class="tutor-onboard-submit-btn tutor-btn tutor-btn-primary tutor-btn-block" data-screen="loading">
								<span><?php esc_html_e( 'Let\'s go', 'tutor' ); ?></span>
								<?php SvgIcon::make()->name( Icon::ARROW_RIGHT_2 )->flip_rtl()->render(); ?>
							</button>
							<p class="tutor-onboard-help-text"><?php esc_html_e( 'Don\'t worry, you can always change these settings later! 😊', 'tutor' ); ?></p>
						</div>
					</form>
				</div>
			</section>

			<section class="tutor-onboard-screen tutor-onboard-screen-loading" data-screen="loading">
				<div class="tutor-onboard-card-loading">
					<?php $loading_text = __( 'The world is changing with AI, but the need for great teachers never will', 'tutor' ); ?>
					<span class="tutor-onboard-loading-text" data-text="<?php echo esc_attr( $loading_text ); ?>">
						<?php echo esc_html( $loading_text ); ?>
					</span>
				</div>
			</section>
		</div>
		<?php wp_print_scripts( 'tutor-script' ); ?>
	</body>
</html>
