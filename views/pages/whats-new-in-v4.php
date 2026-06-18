<?php
/**
 * What's new in v4
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use Tutor\Helpers\UrlHelper;

$has_pro            = tutor()->has_pro;
$tutor_pricing_page = 'https://tutorlms.com/pricing';
$tutor_home_page    = 'https://tutorlms.com';

$action_button_text = $has_pro ? __( 'Learn more', 'tutor' ) : __( 'Get Pro', 'tutor' );
$action_button_url  = $has_pro ? $tutor_pricing_page : $tutor_home_page;

$render_action_button = function () use ( $action_button_text, $action_button_url ) {
	?>
	<a href="<?php echo esc_url( $action_button_url ); ?>" target="_blank" class="tutor-section-action">
		<?php echo esc_html( $action_button_text ); ?>
	</a>
	<?php
};

?>
<div class="tutor-whats-new" data-tutor-theme="light">
	<img class="tutor-hero-image" src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/whats-new-hero.webp' ) ); ?>" alt="What's new in v4" class="tutor-whats-new-in-v4-image">
	<div class="tutor-section-layout">
		<!-- Dashboard and Navigation -->
		<section class="tutor-section-wrapper tutor-section-dashboard">
			<div class="tutor-section-title">
				<div class="tutor-section-title-left">
					<p>
						<?php esc_html_e( 'Dashboard and Navigation', 'tutor' ); ?>
					</p>
					<h2>
						<?php esc_html_e( 'Designed for flow.', 'tutor' ); ?>
					</h2>
				</div>
				<div class="tutor-section-title-right">
					<p>
						<?php esc_html_e( 'Every screen, every interaction, every detail has been reconsidered, so students can focus on learning instead of figuring out where to go next.', 'tutor' ); ?>
					</p>
				</div>
			</div>

			<div class="tutor-section-cards">
				<!-- Dashboard -->
				<div class="tutor-section-card">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'New dashboard with clarity & purpose', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'Everything a student needs — enrolled courses, progress, and upcoming content all laid out clearly from the moment they log in.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/dashboard.webp' ) ); ?>" alt="<?php esc_html_e( 'New dashboard with clarity & purpose', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Navigation -->
				<div class="tutor-section-card tutor-section-card-navigation">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Navigation that feels natural', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'A cleaner in-course layout with visible progress, structured topics, and lesson status so students always know where they are and what\'s next.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/navigation.webp' ) ); ?>" alt="<?php esc_html_e( 'Navigation that feels natural', 'tutor' ); ?>">
					</div>
				</div>
			</div>
		</section>

		<!-- Learners First -->
		<section class="tutor-section-wrapper tutor-section-learner">
			<div class="tutor-section-title">
				<div class="tutor-section-title-left">
					<p>
						<?php esc_html_e( 'Learner first design', 'tutor' ); ?>
					</p>
					<h2>
						<?php
						esc_html_e(
							'Everything they need. Exactly where they expect it.',
							'tutor'
						);
						?>
					</h2>
				</div>
				<div class="tutor-section-title-right">
					<p>
						<?php esc_html_e( 'Notes, discussions, resources, lesson comments – all accessible without leaving the lesson. No more tab-switching. No more hunting.', 'tutor' ); ?>
					</p>

					<div>
						<?php $render_action_button(); ?>
					</div>
				</div>
			</div>

			<div class="tutor-section-cards">
				<!-- Courses -->
				<div class="tutor-section-card">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Courses', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'All enrolled courses, progress tracking, and continue-learning shortcuts organized in one clear view.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/courses.webp' ) ); ?>" alt="<?php esc_html_e( 'Courses', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Quiz Attempts -->
				<div class="tutor-section-card">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Quiz attempts', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'A full log of every quiz attempt — scores, correct answers, and time taken, so students always know where they stand.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/quiz-attempts.webp' ) ); ?>" alt="<?php esc_html_e( 'Quiz attempts', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Notes -->
				<div class="tutor-section-card">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Notes', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'Highlight key moments from lessons and videos, jot down thoughts, and build a personal study guide without ever leaving the course.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/notes.webp' ) ); ?>" alt="<?php esc_html_e( 'Notes', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Discussions -->
				<div class="tutor-section-card">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Discussions', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'Ask questions, share thoughts, and get answers from instructors and peers, right inside the course.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/discussions.webp' ) ); ?>" alt="<?php esc_html_e( 'Discussions', 'tutor' ); ?>">
					</div>
				</div>
			</div>
		</section>

		<!-- Interactive Assessments -->
		<section class="tutor-section-wrapper tutor-section-interactive">
			<div class="tutor-section-title">
				<div class="tutor-section-title-left">
					<p>
						<?php esc_html_e( 'Interactive Assessments', 'tutor' ); ?>
					</p>
					<h2>
						<?php
						esc_html_e(
							'5 new ways to make assessment actually fun.',
							'tutor'
						);
						?>
					</h2>
				</div>
				<div class="tutor-section-title-right">
					<p>
						<?php esc_html_e( 'Most LMSs treat assessment as the boring part. Tutor LMS 4.0 turns it into the part students look forward to — with five new interactive quiz types designed to keep them engaged.', 'tutor' ); ?>
					</p>

					<div>
						<?php $render_action_button(); ?>
					</div>
				</div>
			</div>

			<div class="tutor-section-cards">
				<!-- Ordering -->
				<div class="tutor-section-card tutor-section-card-ordering" style="grid-area: ordering;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Ordering', 'tutor' ); ?>
						</h6>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/ordering.webp' ) ); ?>" alt="<?php esc_html_e( 'Ordering', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Image Marking -->
				<div class="tutor-section-card" style="grid-area: image-marking;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Image Marking', 'tutor' ); ?>
						</h6>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/image-marking.webp' ) ); ?>" alt="<?php esc_html_e( 'Image Marking', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Graph -->
				<div class="tutor-section-card" style="grid-area: graph;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Graph', 'tutor' ); ?>
						</h6>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/graph.webp' ) ); ?>" alt="<?php esc_html_e( 'Graph', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Puzzle -->
				<div class="tutor-section-card" style="grid-area: puzzle;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Puzzle', 'tutor' ); ?>
						</h6>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/puzzle.webp' ) ); ?>" alt="<?php esc_html_e( 'Puzzle', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Range -->
				<div class="tutor-section-card" style="grid-area: range;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Range', 'tutor' ); ?>
						</h6>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/range.webp' ) ); ?>" alt="<?php esc_html_e( 'Range', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Pin -->
				<div class="tutor-section-card" style="grid-area: pin;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Pin', 'tutor' ); ?>
						</h6>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/pin.webp' ) ); ?>" alt="<?php esc_html_e( 'Pin', 'tutor' ); ?>">
					</div>
				</div>
			</div>
		</section>

		<!-- Native App -->
		<section class="tutor-section-wrapper tutor-section-native">
			<div class="tutor-section-title">
				<div class="tutor-section-title-center">
					<p>
						<?php esc_html_e( 'Native App Like Experience', 'tutor' ); ?>
					</p>
					<h2>
						<?php
						printf(
							// translators: placeholder is a line break.
							esc_html__( 'Your academy, in  %s your pocket.', 'tutor' ),
							'<br/>'
						);
						?>
					</h2>
					<p>
						<?php
						printf(
							// translators: placeholder is a line break.
							esc_html__( 'A premium mobile experience that feels like a native app — without building one. Optimized for the thumb %s zone, the commute, and every moment learning actually happens.', 'tutor' ),
							'<br/>'
						);
						?>
					</p>
				</div>
			</div>

			<div class="tutor-section-cards">
				<!-- Native App -->
				<div class="tutor-section-card" style="grid-area: native-app;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Native app like experience', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'The mobile interface mirrors a polished native app — smooth transitions, touch-friendly controls, and a layout built for small screens without any compromise.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image tutor-toggle-images">
						<img class="tutor-img-default" src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/native-app.webp' ) ); ?>" alt="<?php esc_html_e( 'Native app like experience', 'tutor' ); ?>">
						<img class="tutor-img-kids" src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/native-app-kids.webp' ) ); ?>" alt="<?php esc_html_e( 'Native app like experience (Kids)', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Navigation -->
				<div class="tutor-section-card" style="grid-area: navigation;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Smoother navigation', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'Students move between courses, lessons, and their profile without friction — every tap takes them exactly where they need to go.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image tutor-toggle-images">
						<img class="tutor-img-default" src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/smooth-navigation.webp' ) ); ?>" alt="<?php esc_html_e( 'Smoother navigation', 'tutor' ); ?>">
						<img class="tutor-img-kids" src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/smooth-navigation-kids.webp' ) ); ?>" alt="<?php esc_html_e( 'Smoother navigation (Kids)', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Learning Mode -->
				<div class="tutor-section-card tutor-section-card-learning-mode" style="grid-area: mode;">
					<button class="tutor-learning-mode-button active" data-mode="default" aria-label="<?php esc_attr_e( 'Switch to modern mode', 'tutor' ); ?>">
						<svg class="progress-border" viewBox="0 0 48 48"><rect x="1" y="1" width="46" height="46" rx="7"></rect></svg>
						<svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="none" viewBox="0 0 38 38"><path fill="#0265e1" d="M27.559 22.623c.745 0 1.35.605 1.35 1.35v2.709a1.35 1.35 0 0 1-1.35 1.35H11.35A1.35 1.35 0 0 1 10 26.682v-2.708c0-.746.605-1.35 1.35-1.351zm.137-4.059a1.213 1.213 0 0 1 0 2.425H11.212a1.213 1.213 0 0 1 0-2.425zM27.56 10c.745 0 1.35.605 1.35 1.35v4.502a1.35 1.35 0 0 1-1.35 1.351H11.35A1.35 1.35 0 0 1 10 15.853V11.35c0-.746.605-1.35 1.35-1.351z"/><path fill="#fcfdff" d="M13.56 23.57c.373 0 .676.303.676.676v2.242a.676.676 0 0 1-.676.675h-2.148a.676.676 0 0 1-.676-.675v-2.242c0-.373.303-.675.676-.675zM12.556 12.28a.45.45 0 0 1 .696 0l2.731 3.325 2.01-2.538a.45.45 0 0 1 .706 0l3.284 4.15H11.358c-1.14 0-1.768-1.327-1.044-2.209zm14.554-1.364a.9.9 0 1 1 0 1.8.9.9 0 0 1 0-1.8"/></svg>
					</button>
					<button class="tutor-learning-mode-button" data-mode="kids" aria-label="<?php esc_attr_e( 'Switch to kids mode', 'tutor' ); ?>">
						<svg class="progress-border" viewBox="0 0 48 48"><rect x="1" y="1" width="46" height="46" rx="7"></rect></svg>
						<svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="none" viewBox="0 0 38 38"><g><rect width="20.561" height="7.833" x="9.672" y="9" fill="#ffe129" rx="1.201"/><g filter="url(#a)"><path fill="#58cd04" d="M9.672 19.212a.9.9 0 0 1 .9-.9h18.76a.9.9 0 0 1 .9.9v.836a.9.9 0 0 1-.9.9h-18.76a.9.9 0 0 1-.9-.9z"/></g><g filter="url(#b)"><rect width="20.561" height="5.882" x="9.672" y="22.725" fill="#51be02" rx="1.801"/></g><rect width="4.414" height="4.53" x="10.613" y="23.399" fill="#fff" rx="2.207"/><path fill="#fc7501" d="M24.163 11.155c.077 0 .139.061.139.138a4.346 4.346 0 0 1-8.69 0 .138.138 0 1 1 .277 0 4.068 4.068 0 0 0 8.134 0 .14.14 0 0 1 .14-.138m-4.887-.63a.14.14 0 0 1 .14.138c0 .234-.131.432-.318.568a1.2 1.2 0 0 1-.71.214c-.27 0-.522-.079-.71-.214-.186-.136-.317-.334-.317-.568a.139.139 0 1 1 .278 0c0 .122.068.245.203.343a.94.94 0 0 0 .547.161.94.94 0 0 0 .546-.161c.134-.098.203-.22.203-.343 0-.077.062-.138.138-.138m1.16-.072a.14.14 0 0 1 .132.146c-.006.121.057.248.186.351.13.104.32.178.539.189a.94.94 0 0 0 .553-.135c.139-.09.213-.21.22-.332a.14.14 0 0 1 .278.014c-.012.233-.153.425-.346.55a1.2 1.2 0 0 1-.719.18 1.2 1.2 0 0 1-.699-.249c-.18-.144-.3-.349-.289-.582a.14.14 0 0 1 .145-.132"/><path fill="#ff6ccb" d="M31.416 18.473c-.505.936 0 1.852.325 2.604.114.265.227.517.42.621s.464.06.749.01c.807-.14 1.85-.222 2.355-1.158s-.196-2.17-1.547-1.738c-.38-1.367-1.797-1.275-2.302-.339M5.196 21.36a.3.3 0 0 0-.035-.164l-.848-1.573a.3.3 0 0 1 .256-.443l1.783-.048a.3.3 0 0 0 .16-.051l1.5-1.013a.3.3 0 0 1 .466.27l-.128 1.808a.3.3 0 0 0 .035.164l.848 1.573a.3.3 0 0 1-.255.443l-1.784.048a.3.3 0 0 0-.16.05l-1.5 1.014a.3.3 0 0 1-.466-.271z"/></g><defs><filter id="a" width="20.561" height="2.786" x="9.672" y="18.312" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/><feOffset dy=".15"/><feComposite in2="hardAlpha" operator="out"/><feColorMatrix values="0 0 0 0 0.231373 0 0 0 0 0.54902 0 0 0 0 0 0 0 0 1 0"/><feBlend in2="BackgroundImageFix" result="effect1_dropShadow_21950_196354"/><feBlend in="SourceGraphic" in2="effect1_dropShadow_21950_196354" result="shape"/></filter><filter id="b" width="20.561" height="6.182" x="9.672" y="22.725" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/><feOffset dy=".3"/><feComposite in2="hardAlpha" operator="out"/><feColorMatrix values="0 0 0 0 0.231373 0 0 0 0 0.54902 0 0 0 0 0 0 0 0 1 0"/><feBlend in2="BackgroundImageFix" result="effect1_dropShadow_21950_196354"/><feBlend in="SourceGraphic" in2="effect1_dropShadow_21950_196354" result="shape"/></filter></defs></svg>
					</button>
						
					<div class="tutor-section-card-title tutor-lm-text-transition"
						data-default-title="<?php esc_attr_e( 'Modern Modes', 'tutor' ); ?>"
						data-default-desc="<?php esc_attr_e( 'A modern, distraction-free learning experience.', 'tutor' ); ?>"
						data-kids-title="<?php esc_attr_e( 'Kids Mode', 'tutor' ); ?>"
						data-kids-desc="<?php esc_attr_e( 'A fun, engaging and distraction-free learning experience.', 'tutor' ); ?>">
						<h6>
							<?php esc_html_e( 'Modern Modes', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'A modern, distraction-free learning experience.', 'tutor' ); ?>
						</p>
					</div>
				</div>
			</div>
		</section>

		<!-- Accessibility -->
		<section class="tutor-section-wrapper tutor-section-a11y">
			<div class="tutor-section-title">
				<div class="tutor-section-title-center">
					<p>
						<?php esc_html_e( 'Accessibility', 'tutor' ); ?>
					</p>
					<h2>
						<?php
						printf(
							// translators: placeholder is a line break.
							esc_html__( 'No learner left behind. %s By design.', 'tutor' ),
							'<br/>'
						);
						?>
					</h2>
					<p>
						<?php
						printf(
							// translators: placeholder is a line break.
							esc_html__( 'A premium mobile experience that feels like a native app — without building one. Optimized for the thumb %s zone, the commute, and every moment learning actually happens.', 'tutor' ),
							'<br/>'
						);
						?>
					</p>
				</div>
			</div>

			<div class="tutor-section-cards">
				<!-- Mode with preference -->
				<div class="tutor-section-card" style="grid-area: mode-preference;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Mode with preference', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'Students can switch between Modern, Dark, and Kids modes to match their personal comfort and learning environment.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-comparison" style="--pos: 50%;">
						<div class="tutor-comparison-inner">
							<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/dashboard-dark.webp' ) ); ?>" alt="<?php esc_html_e( 'Dark Mode', 'tutor' ); ?>">
							<div class="tutor-comparison-img-light-wrapper">
								<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/dashboard-light.webp' ) ); ?>" alt="<?php esc_html_e( 'Light Mode', 'tutor' ); ?>">
							</div>
							<div class="tutor-comparison-handle-line"></div>
						</div>
						<input type="range" min="0" max="100" value="50" class="tutor-comparison-slider" oninput="this.parentNode.style.setProperty('--pos', this.value + '%')">
						<div class="tutor-comparison-handle-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="19" height="9" fill="none" viewBox="0 0 19 9"><path fill="#0f0f0f" d="M5.429 7.982V.703q0-.298-.161-.501Q5.108 0 4.893 0a.476.476 0 0 0-.281.105l-4.37 3.64a.66.66 0 0 0-.182.264.95.95 0 0 0 0 .668q.06.157.181.264l4.37 3.64a.5.5 0 0 0 .14.079.4.4 0 0 0 .142.026q.214 0 .375-.203a.78.78 0 0 0 .16-.5M13.571 7.982V.703q0-.298.161-.501.16-.202.375-.202.067 0 .142.026t.14.08l4.37 3.639q.12.105.18.264a.95.95 0 0 1 0 .668.64.64 0 0 1-.18.264l-4.37 3.64a.5.5 0 0 1-.14.079.4.4 0 0 1-.142.026q-.214 0-.375-.203a.78.78 0 0 1-.16-.5"/></svg>
						</div>
					</div>
				</div>

				<!-- Font -->
				<div class="tutor-section-card tutor-section-feature-card" style="grid-area: font;">
					<div class="tutor-section-card-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-opacity=".7" stroke-width="1.7" d="M24 28V14.667m2.667 10L24 28l-2.667-3.334m5.334-7.333L24 14.667l-2.667 2.666M12 6.667v16m0 0H9.333m2.667 0h2.667M20 9.333V6.667H4v2.666"/></svg>
					</div>

					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Font size control', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'Adjustable font sizes let students set text to a comfortable reading size, from compact to large, without affecting the overall layout.', 'tutor' ); ?>
						</p>
					</div>
				</div>

				<!-- Contrast -->
				<div class="tutor-section-card tutor-section-feature-card" style="grid-area: contrast;">
					<div class="tutor-section-card-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-opacity=".7" stroke-width="1.7" d="M16 28.8c7.07 0 12.8-5.73 12.8-12.8S23.07 3.2 16 3.2 3.2 8.93 3.2 16 8.93 28.8 16 28.8"/><path fill="#333741" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-opacity=".7" stroke-width="1.7" d="M15.999 23.68a7.68 7.68 0 1 0 0-15.36z"/></svg>
					</div>

					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'High contrast', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'A high contrast toggle that sharpens visibility for students with low vision or anyone learning in challenging lighting conditions', 'tutor' ); ?>
						</p>
					</div>
				</div>

				<!-- Vision -->
				<div class="tutor-section-card tutor-section-feature-card" style="grid-area: vision;">
					<div class="tutor-section-card-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-opacity=".7" stroke-width="1.7" d="M29.334 16S23.363 24 16 24 2.667 16 2.667 16 8.637 8 16 8s13.334 8 13.334 8"/><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-opacity=".7" stroke-width="1.7" d="M18.828 18.828a4 4 0 1 1-5.656-5.656 4 4 0 0 1 5.656 5.656"/></svg>
					</div>
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Multiple vision mode', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'Color filter options including Protanopia, Deuteranopia, and Deuteranomaly support students with different types of color blindness.', 'tutor' ); ?>
						</p>
					</div>
				</div>

				<!-- Motion -->
				<div class="tutor-section-card tutor-section-feature-card" style="grid-area: motion;">
					<div class="tutor-section-card-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32"><path fill="#333741" d="M8.985 24a.78.78 0 0 1-.57-.23.77.77 0 0 1-.23-.572.78.78 0 0 1 .8-.798H19.6q-1.36-.987-2.008-2.25-.648-1.261-.755-2.55h-5.021a.78.78 0 0 1-.571-.23.77.77 0 0 1-.23-.572.78.78 0 0 1 .8-.798h5.022q.17-1.35.816-2.613.65-1.26 1.947-2.187H3.2a.784.784 0 0 1-.8-.802.76.76 0 0 1 .23-.57.78.78 0 0 1 .57-.228H24q2.988 0 5.094 2.106 2.106 2.102 2.106 5.09 0 2.986-2.106 5.095-2.106 2.11-5.094 2.11zM24 22.4q2.307 0 3.953-1.646Q29.6 19.107 29.6 16.8t-1.647-3.953T24 11.2t-3.954 1.647T18.4 16.8t1.646 3.954T24 22.4M1.6 17.6a.784.784 0 0 1-.8-.802.76.76 0 0 1 .23-.57A.78.78 0 0 1 1.6 16h7.016q.339 0 .57.23.23.231.23.572a.77.77 0 0 1-.23.57.78.78 0 0 1-.57.228zM3.2 24a.784.784 0 0 1-.8-.802.76.76 0 0 1 .23-.57.78.78 0 0 1 .57-.228h2.585q.34 0 .57.23a.77.77 0 0 1 .229.572.78.78 0 0 1-.8.798z"/><path stroke="#000" stroke-opacity=".7" stroke-width="1.7" d="M21.593 22.796q.595.24 1.242.354h-.756zm1.244-12.346a6 6 0 0 0-1.188.33l.464-.33z"/></svg>
					</div>
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Reduced motion', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'Students sensitive to motion can reduce or disable animations and hover effects across the entire platform.', 'tutor' ); ?>
						</p>
					</div>
				</div>
			</div>
		</section>

		<!-- Instructor Dashboard -->
		<section class="tutor-section-wrapper tutor-section-instructor-dashboard">
			<div class="tutor-section-title">
				<div class="tutor-section-title-left">
					<p>
						<?php esc_html_e( 'Instructor Dashboard', 'tutor' ); ?>
					</p>
					<h2>
						<?php
						printf(
							// translators: placeholder is a line break.
							esc_html__( 'Run your entire academy  %s from one screen.', 'tutor' ),
							'<br/>'
						);
						?>
					</h2>
				</div>

				<div class="tutor-section-title-right">
					<p>
						<?php esc_html_e( 'Quiz attempts, assignments, revenue, student progress — everything tracked, everything visible, without jumping between dashboards. Spend less time administrating and more time doing what you\'re actually good at.', 'tutor' ); ?>
					</p>

					<div>
						<?php $render_action_button(); ?>
					</div>
				</div>
			</div>

			<div class="tutor-section-cards">
				<!-- Learners -->
				<div class="tutor-section-card" style="grid-area: learners;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Learners', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'A full student roster with registration dates, enrolled courses, and activity — everything needed to stay on top of who\'s learning what.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/learners.webp' ) ); ?>" alt="<?php esc_html_e( 'Learners', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Assignments -->
				<div class="tutor-section-card" style="grid-area: assignments;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Assignments', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'Review, grade, and track every submitted assignment across all courses, with pass marks, deadlines, and results in one view.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/assignments.webp' ) ); ?>" alt="<?php esc_html_e( 'Assignments', 'tutor' ); ?>">
					</div>
				</div>

				<!-- Announcements -->
				<div class="tutor-section-card" style="grid-area: announcements;">
					<div class="tutor-section-card-title">
						<h6>
							<?php esc_html_e( 'Announcements', 'tutor' ); ?>
						</h6>
						<p>
							<?php esc_html_e( 'Send course-specific announcements to students directly from the dashboard, keeping everyone informed without leaving the platform.', 'tutor' ); ?>
						</p>
					</div>

					<div class="tutor-section-card-image">
						<img src="<?php echo esc_url( UrlHelper::asset( 'images/whats-new/announcements.webp' ) ); ?>" alt="<?php esc_html_e( 'Announcements', 'tutor' ); ?>">
					</div>
				</div>
			</div>
		</section>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const modeButtons = document.querySelectorAll('.tutor-learning-mode-button');
	const toggleImages = document.querySelectorAll('.tutor-toggle-images');
	const textContainer = document.querySelector('.tutor-lm-text-transition');
	
	if (!modeButtons.length || !textContainer) return;
	
	const titleEl = textContainer.querySelector('h6');
	const descEl = textContainer.querySelector('p');

	const switchMode = (mode) => {
		// Update buttons
		modeButtons.forEach(btn => {
			btn.classList.remove('active');
			// Force reflow to reset animation
			void btn.offsetWidth;
		});
		
		const activeBtn = document.querySelector(`.tutor-learning-mode-button[data-mode="${mode}"]`);
		if(activeBtn) activeBtn.classList.add('active');

		// Update images
		toggleImages.forEach(container => {
			if (mode === 'kids') {
				container.classList.add('show-kids');
			} else {
				container.classList.remove('show-kids');
			}
		});

		// Update text with fade
		textContainer.classList.add('fading');
		setTimeout(() => {
			if (mode === 'kids') {
				titleEl.textContent = textContainer.getAttribute('data-kids-title');
				descEl.textContent = textContainer.getAttribute('data-kids-desc');
			} else {
				titleEl.textContent = textContainer.getAttribute('data-default-title');
				descEl.textContent = textContainer.getAttribute('data-default-desc');
			}
			textContainer.classList.remove('fading');
		}, 300); // Wait for fade out
	};

	modeButtons.forEach(btn => {
		btn.addEventListener('click', (e) => {
			const mode = e.currentTarget.getAttribute('data-mode');
			switchMode(mode);
		});

		// Handle animation end
		const rect = btn.querySelector('.progress-border rect');
		if (rect) {
			rect.addEventListener('animationend', (e) => {
				// When animation finishes, automatically switch to the OTHER mode
				if (btn.classList.contains('active')) {
					const nextMode = btn.getAttribute('data-mode') === 'default' ? 'kids' : 'default';
					switchMode(nextMode);
				}
			});
		}
	});
});
</script>