<?php
/**
 * Welcome page
 *
 * @package Tutor\Views
 * @subpackage Tutor\Welcome
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$image_url = 'https://tutorlms.com/wp-content/uploads/2024/11';
?>

<style type="text/css">
*,
::after,
::before {
	box-sizing: border-box;
}

.notice, .tutor-user-registration-notice-wrapper, #wpbody-content .error {
	display: none;
}

.tutor-welcome-card {
	background: #F8F8F8;
	border-radius: 18px;
}

.tutor-hide-welcome-button {
	position: absolute;
	top: -20px;
	right: 12px;
	display: flex;
	align-items: center;
	gap: 4px;
	color: #ffffff;
	border-color: rgba(255,255,255,0.6);
}

.tutor-hide-welcome-button:hover {
	background-color: #ffffff;
	color: #3E64DE;
}

.tutor-lms-welcome-page {
	margin-left: -20px;
}

.tutor-lms-welcome-page img {
	max-width: 100%;
}

.tutor-lms-welcome-page .tutor-header-section {
	padding: 64px 0px 90px;
	background-color: #0049F8;
}

.tutor-header-section .tutor-container {
	position: relative;
}

.tutor-header-section .banner-content {
	margin-top: 50px;
}

.tutor-header-section .banner-title {
	font-size: 32px;
	line-height: 50px;
	font-weight: 400;
	color: #ffffff;
	margin: 0px;
}
.tutor-header-section .banner-title strong {
	font-weight: 700;
}

.tutor-builder-section {
	position: relative;
	background: #F8F8F8;
	border-radius: 18px;
	margin-top: -124px;
}

.tutor-section-title {
	font-size: 30px;
	line-height: 36px;
	color: #212327;
	margin-top: 0px;
	margin-bottom: 16px;
}

.tutor-section-title.ai-studio {
	background: linear-gradient(73.09deg, #FF9645 18.05%, #FF6471 30.25%, #CF6EBD 55.42%, #A477D1 71.66%, #3E64DE 97.9%);
	background-clip: text;
	-webkit-background-clip: text;
	-webkit-text-fill-color: transparent;
}

.tutor-section-description {
	font-size: 16px;
	line-height: 24px;
	color: #5B616F;
	margin: 0px;
}

@media (max-width: 1560px) {
	.tutor-container {
		max-width: 1080px;
	}
}

@media (max-width: 767px) {
	.tutor-lms-welcome-page {
		margin-left: -10px;
	}
}

</style>

<div class="tutor-lms-welcome-page">
	<section class="tutor-header-section">
		<div class="tutor-container">
			<div class="tutor-row">
				<div class="tutor-col-lg-5">
					<div class="banner-content">
						<div class="tutor-mb-32">
							<a href="https://tutorlms.com" class="tutor-d-inline-block">
								<img src="<?php echo esc_url( tutor()->url ) . 'assets/images/tutor-logo-white.svg'; ?>" alt="Tutor LMS" />
							</a>
						</div>
						<h3 class="banner-title">
							Welcome to <strong>Tutor LMS <?php echo esc_html( TUTOR_VERSION ); ?>!</strong><br/>Redefining eLearning on WordPress
						</h3>
					</div>
				</div>

				<div class="tutor-col-lg-7">
					<button class="tutor-btn tutor-btn-outline-primary tutor-btn-lg tutor-hide-welcome-button">
						<i class="tutor-icon-times"></i>
						<?php esc_html_e( "Don't Show Again", 'tutor' ); ?>
					</button>
					<img src="<?php echo esc_url( $image_url ) . '/banner.png'; ?>" alt="banner" class="banner-image" />
				</div>
			</div>
		</div>
	</section>

	<section>
		<div class="tutor-container">
			<div class="tutor-builder-section">
				<div class="tutor-row tutor-align-center">
					<div class="tutor-col-lg-4 tutor-gap-4">
						<div class="tutor-pl-lg-40 tutor-p-24 tutor-p-lg-0">
							<h3 class="tutor-section-title">
								<?php esc_html_e( 'Reimagined Course & Quiz Builder', 'tutor' ); ?>
							</h3>
							<p class="tutor-section-description">
								<?php esc_html_e( "The reimagined course & quiz builder lets instructors craft visually rich, interactive lessons, and quizzes with ease. Add multimedia, captivating quizzes, custom paths, and more to elevate every learner's journey.", 'tutor' ); ?>
							</p>
						</div>
					</div>
					<div class="tutor-col-lg-8">
						<img src="<?php echo esc_url( $image_url ) . '/course-builder.png'; ?>" alt="Course builder" />
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="tutor-mt-32">
		<div class="tutor-container">
			<div class="tutor-row tutor-g-4">
				<div class="tutor-col-lg-5">
					<div class="tutor-welcome-card">
						<div class="tutor-p-24 tutor-p-lg-48 tutor-pb-lg-32">
							<h3 class="tutor-section-title">
								<?php esc_html_e( 'Native eCommerce', 'tutor' ); ?>
							</h3>
							<p class="tutor-section-description">
								<?php esc_html_e( 'Sell courses easily with native payments! Control orders, coupons, and taxes while enjoying secure payments via top gateways—all without relying on third-party tools or dependencies.', 'tutor' ); ?>
							</p>
						</div>
						<div>
							<img src="https://tutorlms.com/wp-content/uploads/2024/11/welcome-ecommerce.png" alt="Ecommerce" />
						</div>
					</div>
				</div>
				<div class="tutor-col-lg-7">
					<div class="tutor-welcome-card tutor-pt-44 tutor-pb-40 tutor-px-32">
						<div class="tutor-row tutor-align-center">
							<div class="tutor-col-md-6">
								<img src="<?php echo esc_url( $image_url ) . '/subscriptions.png'; ?>" alt="Subscriptions" />
							</div>
							<div class="tutor-col-md-6">
								<div class="">
									<h3 class="tutor-section-title">
										<?php esc_html_e( 'Subscriptions', 'tutor' ); ?>
									</h3>
									<p class="tutor-section-description">
										<?php esc_html_e( 'Create a recurring revenue stream with a robust subscription system. Effortlessly handle billing, and renewals, while offering flexible pricing tiers—whether monthly or yearly.', 'tutor' ); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
					<div class="tutor-welcome-card tutor-mt-32">
						<div class="tutor-row tutor-align-center">
							<div class="tutor-col-md-6">
								<img src="<?php echo esc_url( $image_url ) . '/analytics.png'; ?>" alt="Analytics" />
							</div>
							<div class="tutor-col-md-6">
								<div class="tutor-p-24 tutor-pr-md-40">
									<h3 class="tutor-section-title">
										<?php esc_html_e( 'Advanced Analytics', 'tutor' ); ?>
									</h3>
									<p class="tutor-section-description">
										<?php esc_html_e( 'Get detailed insights on courses, students, earnings, statements, and do so much more with advanced analytics.', 'tutor' ); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="tutor-mt-32">
		<div class="tutor-container">
			<div class="tutor-welcome-card">
				<div class="tutor-row tutor-align-center">
					<div class="tutor-col-lg-4 tutor-gap-4">
						<div class="tutor-pl-lg-60 tutor-p-24 tutor-p-lg-0">
							<h3 class="tutor-section-title ai-studio">
								<?php esc_html_e( 'AI Studio', 'tutor' ); ?>
							</h3>
							<p class="tutor-section-description">
								<?php esc_html_e( 'Tap into the power of AI to save your course creation time and improve course quality. Generate course outlines, images, and contextual content at the click of a button.', 'tutor' ); ?>
							</p>
						</div>
					</div>
					<div class="tutor-col-lg-8">
						<img src="<?php echo esc_url( $image_url ) . '/ai-studio.png'; ?>" alt="AI Studio" />
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="tutor-mt-32">
		<div class="tutor-container">
			<div class="tutor-row tutor-g-4">
				<div class="tutor-col-lg-7">
					<div class="tutor-welcome-card tutor-pt-40 tutor-pl-lg-32">
						<div class="tutor-row tutor-align-center">
							<div class="tutor-col-md-6">
								<img src="<?php echo esc_url( $image_url ) . '/design.png'; ?>" alt="Design" />
							</div>
							<div class="tutor-col-md-6">
								<div class="tutor-p-24 tutor-pr-md-40">
									<h3 class="tutor-section-title">
										<?php esc_html_e( 'Unified Design', 'tutor' ); ?>
									</h3>
									<p class="tutor-section-description">
										<?php esc_html_e( 'A cohesive, intuitive design that enhances user experience across all aspects of course creation and management.', 'tutor' ); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="tutor-col-lg-5">
					<div class="tutor-welcome-card tutor-p-40 tutor-d-flex tutor-align-center" style="height: 100%;">
						<div class="tutor-row tutor-g-4 tutor-align-center">
							<div class="tutor-col-md-4">
								<img src="<?php echo esc_url( $image_url ) . '/add-more.png'; ?>" alt="Add more" />
							</div>
							<div class="tutor-col-md-8">
								<div class="tutor-pr-md-40">
									<h3 class="tutor-section-title">
										<?php esc_html_e( 'And more…', 'tutor' ); ?>
									</h3>
									<p class="tutor-section-description">
										<?php esc_html_e( 'Explore additional features designed to elevate your eLearning experience.', 'tutor' ); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="tutor-mt-80">
		<div class="tutor-container">
			<div class="tutor-d-flex tutor-flex-column tutor-flex-md-row tutor-justify-center tutor-gap-4">
				<a class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-d-block" href="<?php echo esc_url( admin_url( 'admin.php?page=tutor' ) ); ?>">
					<?php esc_html_e( "Let's Start Building", 'tutor' ); ?>
				</a>
				<a target="_blank" class="tutor-btn tutor-btn-outline-primary tutor-btn-lg tutor-d-block" 
					href="https://tutorlms.com/free-vs-pro/?utm_source=wizard&utm_medium=wp_dashboard&utm_campaign=free_vs_pro#pricing-section" rel="noreferrer noopener">
					<?php esc_html_e( 'Compare Free vs Pro', 'tutor' ); ?>
				</a>
			</div>
		</div>
	</section>
</div>

<script>
	jQuery(document).ready(function($) {
		$('.tutor-hide-welcome-button').on('click', function(e) {

			const courseUrl = '<?php echo esc_url( admin_url( 'admin.php?page=tutor' ) ); ?>';
			const nonce = '<?php echo esc_attr( wp_create_nonce( 'tutor_nonce_action' ) ); ?>';

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					_tutor_nonce: nonce,
					action: 'tutor_do_not_show_feature_page'
				},
				beforeSend: function () {
					e.target.classList.add('is-loading');
					e.target.setAttribute('disabled', true);
				},
				success: function(response) {
					window.location.href = courseUrl;
				},
				complete: function() {
					e.target.classList.remove('is-loading');
					e.target.removeAttribute('disabled');
				}
			});
		});
	});
</script>
