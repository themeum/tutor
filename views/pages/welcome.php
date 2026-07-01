<?php
/**
 * Welcome page
 *
 * @package Tutor\Views
 * @subpackage Tutor\Welcome
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

$has_pro            = tutor()->has_pro;
$tutor_pricing_page = 'https://tutorlms.com/pricing';
$tutor_home_page    = 'https://tutorlms.com';
$asset_base         = 'https://tutor-lms.s3.us-east-1.amazonaws.com/whats-new/';

$action_button_text = $has_pro ? __( 'Learn more', 'tutor' ) : __( 'Get Pro', 'tutor' );
$action_button_url  = $has_pro ? $tutor_home_page : $tutor_pricing_page;

$render_action_button = function ( $text, $url ) {
	?>
	<a href="<?php echo esc_url( $url ); ?>" target="_blank" class="tutor-section-action">
		<?php echo esc_html( $text ); ?>
	</a>
	<?php
};

/**
 * Render a single feature card (title + description + image).
 *
 * $card keys: title, desc, image (relative to $asset_base), class (optional), grid_area (optional), icon (optional).
 */
$render_card = function ( $card ) use ( $asset_base ) {
	$classes = array_filter( array( 'tutor-section-card', $card['class'] ?? '' ) );
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo isset( $card['grid_area'] ) ? ' style="grid-area: ' . esc_attr( $card['grid_area'] ) . ';"' : ''; ?>>
		<?php if ( ! empty( $card['icon'] ) ) : ?>
			<div class="tutor-section-card-icon"><?php echo $card['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted inline SVG. ?></div>
		<?php endif; ?>
		<?php if ( ! empty( $card['title'] ) || ! empty( $card['desc'] ) ) : ?>
			<div class="tutor-section-card-title">
				<?php if ( ! empty( $card['title'] ) ) : ?>
					<h6><?php echo esc_html( $card['title'] ); ?></h6>
				<?php endif; ?>
				<?php if ( ! empty( $card['desc'] ) ) : ?>
					<p><?php echo esc_html( $card['desc'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $card['image'] ) ) : ?>
			<div class="tutor-section-card-image">
				<img src="<?php echo esc_url( $asset_base . $card['image'] ); ?>" alt="<?php echo esc_attr( $card['title'] ?? '' ); ?>">
			</div>
		<?php endif; ?>
	</div>
	<?php
};

// --------------------------------------------------------------------------
// Card data. Grid placement is handled inline via style="grid-area" per card,
// not inline styles.
// --------------------------------------------------------------------------

$dashboard_cards = array(
	array(
		'title' => __( 'New dashboard with clarity & purpose', 'tutor' ),
		'desc'  => __( 'Everything a student needs — enrolled courses, progress, and upcoming content all laid out clearly from the moment they log in.', 'tutor' ),
		'image' => 'dashboard.webp',
	),
	array(
		'title' => __( 'Navigation that feels natural', 'tutor' ),
		'desc'  => __( 'A cleaner in-course layout with visible progress, structured topics, and lesson status so students always know where they are and what\'s next.', 'tutor' ),
		'image' => 'navigation.webp',
		'class' => 'tutor-section-card-navigation',
	),
);

$learner_cards = array(
	array(
		'title' => __( 'Courses', 'tutor' ),
		'desc'  => __( 'All enrolled courses, progress tracking, and continue-learning shortcuts organized in one clear view.', 'tutor' ),
		'image' => 'courses.webp',
	),
	array(
		'title' => __( 'Quiz attempts', 'tutor' ),
		'desc'  => __( 'A full log of every quiz attempt — scores, correct answers, and time taken, so students always know where they stand.', 'tutor' ),
		'image' => 'quiz-attempts.webp',
	),
	array(
		'title' => __( 'Notes', 'tutor' ),
		'desc'  => __( 'Highlight key moments from lessons and videos, jot down thoughts, and build a personal study guide without ever leaving the course.', 'tutor' ),
		'image' => 'notes.webp',
	),
	array(
		'title' => __( 'Discussions', 'tutor' ),
		'desc'  => __( 'Ask questions, share thoughts, and get answers from instructors and peers, right inside the course.', 'tutor' ),
		'image' => 'discussions.webp',
	),
);

$interactive_cards = array(
	array(
		'title'     => __( 'Ordering', 'tutor' ),
		'image'     => 'ordering.webp',
		'class'     => 'tutor-section-card-ordering',
		'grid_area' => 'ordering',
	),
	array(
		'title'     => __( 'Image Marking', 'tutor' ),
		'image'     => 'image-marking.webp',
		'grid_area' => 'image-marking',
	),
	array(
		'title'     => __( 'Graph', 'tutor' ),
		'image'     => 'graph.webp',
		'grid_area' => 'graph',
	),
	array(
		'title'     => __( 'Puzzle', 'tutor' ),
		'image'     => 'puzzle.webp',
		'grid_area' => 'puzzle',
	),
	array(
		'title'     => __( 'Range', 'tutor' ),
		'image'     => 'range.webp',
		'grid_area' => 'range',
	),
	array(
		'title'     => __( 'Pin', 'tutor' ),
		'image'     => 'pin.webp',
		'grid_area' => 'pin',
	),
);

$instructor_cards = array(
	array(
		'title'     => __( 'Learners', 'tutor' ),
		'desc'      => __( 'A full student roster with registration dates, enrolled courses, and activity — everything needed to stay on top of who\'s learning what.', 'tutor' ),
		'image'     => 'learners.webp',
		'grid_area' => 'learners',
	),
	array(
		'title'     => __( 'Assignments', 'tutor' ),
		'desc'      => __( 'Review, grade, and track every submitted assignment across all courses, with pass marks, deadlines, and results in one view.', 'tutor' ),
		'image'     => 'assignments.webp',
		'grid_area' => 'assignments',
	),
	array(
		'title'     => __( 'Announcements', 'tutor' ),
		'desc'      => __( 'Send course-specific announcements to students directly from the dashboard, keeping everyone informed without leaving the platform.', 'tutor' ),
		'image'     => 'announcements.webp',
		'grid_area' => 'announcements',
	),
);

// A11y feature card icons are small trusted inline SVGs (decorative, hidden from AT).
$a11y_feature_cards = array(
	array(
		'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-opacity=".7" stroke-width="1.7" d="M24 28V14.667m2.667 10L24 28l-2.667-3.334m5.334-7.333L24 14.667l-2.667 2.666M12 6.667v16m0 0H9.333m2.667 0h2.667M20 9.333V6.667H4v2.666"/></svg>',
		'title'     => __( 'Font size control', 'tutor' ),
		'desc'      => __( 'Adjustable font sizes let students set text to a comfortable reading size, from compact to large, without affecting the overall layout.', 'tutor' ),
		'class'     => 'tutor-section-feature-card',
		'grid_area' => 'font',
	),
	array(
		'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-opacity=".7" stroke-width="1.7" d="M16 28.8c7.07 0 12.8-5.73 12.8-12.8S23.07 3.2 16 3.2 3.2 8.93 3.2 16 8.93 28.8 16 28.8"/><path fill="#333741" stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-opacity=".7" stroke-width="1.7" d="M15.999 23.68a7.68 7.68 0 1 0 0-15.36z"/></svg>',
		'title'     => __( 'High contrast', 'tutor' ),
		'desc'      => __( 'A high contrast toggle that sharpens visibility for students with low vision or anyone learning in challenging lighting conditions', 'tutor' ),
		'class'     => 'tutor-section-feature-card',
		'grid_area' => 'contrast',
	),
	array(
		'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-opacity=".7" stroke-width="1.7" d="M29.334 16S23.363 24 16 24 2.667 16 2.667 16 8.637 8 16 8s13.334 8 13.334 8"/><path stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-opacity=".7" stroke-width="1.7" d="M18.828 18.828a4 4 0 1 1-5.656-5.656 4 4 0 0 1 5.656 5.656"/></svg>',
		'title'     => __( 'Multiple vision mode', 'tutor' ),
		'desc'      => __( 'Color filter options including Protanopia, Deuteranopia, and Deuteranomaly support students with different types of color blindness.', 'tutor' ),
		'class'     => 'tutor-section-feature-card',
		'grid_area' => 'vision',
	),
	array(
		'icon'      => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path fill="#333741" d="M8.985 24a.78.78 0 0 1-.57-.23.77.77 0 0 1-.23-.572.78.78 0 0 1 .8-.798H19.6q-1.36-.987-2.008-2.25-.648-1.261-.755-2.55h-5.021a.78.78 0 0 1-.571-.23.77.77 0 0 1-.23-.572.78.78 0 0 1 .8-.798h5.022q.17-1.35.816-2.613.65-1.26 1.947-2.187H3.2a.784.784 0 0 1-.8-.802.76.76 0 0 1 .23-.57.78.78 0 0 1 .57-.228H24q2.988 0 5.094 2.106 2.106 2.102 2.106 5.09 0 2.986-2.106 5.095-2.106 2.11-5.094 2.11zM24 22.4q2.307 0 3.953-1.646Q29.6 19.107 29.6 16.8t-1.647-3.953T24 11.2t-3.954 1.647T18.4 16.8t1.646 3.954T24 22.4M1.6 17.6a.784.784 0 0 1-.8-.802.76.76 0 0 1 .23-.57A.78.78 0 0 1 1.6 16h7.016q.339 0 .57.23.23.231.23.572a.77.77 0 0 1-.23.57.78.78 0 0 1-.57.228zM3.2 24a.784.784 0 0 1-.8-.802.76.76 0 0 1 .23-.57.78.78 0 0 1 .57-.228h2.585q.34 0 .57.23a.77.77 0 0 1 .229.572.78.78 0 0 1-.8.798z"/><path stroke="#000" stroke-opacity=".7" stroke-width="1.7" d="M21.593 22.796q.595.24 1.242.354h-.756zm1.244-12.346a6 6 0 0 0-1.188.33l.464-.33z"/></svg>',
		'title'     => __( 'Reduced motion', 'tutor' ),
		'desc'      => __( 'Students sensitive to motion can reduce or disable animations and hover effects across the entire platform.', 'tutor' ),
		'class'     => 'tutor-section-feature-card',
		'grid_area' => 'motion',
	),
);
?>

<style type="text/css">
.tutor-whats-new {
	margin-left: -20px;
	background-color: rgb(255, 255, 255);
	position: relative;
}
.tutor-whats-new .tutor-whats-new-dismiss-bar {
	position: absolute;
	top: 24px;
	width: 100%;
	display: flex;
	justify-content: flex-end;
	z-index: 10;

	a {
		margin-inline-end: 24px;
		max-width: fit-content;
	}
}
.tutor-whats-new .tutor-hero-image {
	width: 100%;
	height: auto;
	max-width: 100%;
}
.tutor-whats-new .tutor-section-layout {
	display: flex;
	flex-direction: column;
	gap: 128px;
	max-width: 1280px;
	/* width: 100%; */
	margin: 0 auto;
	padding: 64px 20px;
}
.tutor-whats-new .tutor-section-title {
	display: flex;
	flex-direction: row;
	align-items: flex-end;
	justify-content: space-between;
	gap: 24px;
}
.tutor-whats-new .tutor-section-title-left, .tutor-whats-new .tutor-section-title-center {
	display: flex;
	flex-direction: column;
	gap: 16px;
	flex-basis: 50%;
}
.tutor-whats-new .tutor-section-title-left p, .tutor-whats-new .tutor-section-title-center p {
	font-size: 0.875rem;
	line-height: 1.125rem;
	letter-spacing: 0.125em;
	font-weight: 400;
	text-transform: uppercase;
	color: rgba(0, 0, 0, 0.68);
	margin: 0;
}
.tutor-whats-new .tutor-section-title-left h2, .tutor-whats-new .tutor-section-title-center h2 {
	font-size: 3rem;
	line-height: 3.5rem;
	letter-spacing: -0.02em;
	font-weight: 500;
	color: rgb(15, 15, 15);
	margin: 0;
}
.tutor-whats-new .tutor-section-title-center {
	flex-basis: 100%;
	text-align: center;
	align-items: center;
}
.tutor-whats-new .tutor-section-title-center p:last-of-type {
	text-transform: none;
	letter-spacing: 0em;
}
.tutor-whats-new .tutor-section-title-right {
	display: flex;
	flex-direction: column;
	gap: 20px;
	max-width: 400px;
	width: 100%;
	flex-basis: 50%;
}
.tutor-whats-new .tutor-section-title-right p {
	font-size: 1rem;
	line-height: 1.375rem;
	letter-spacing: 0em;
	font-weight: 400;
	color: rgba(0, 0, 0, 0.68);
	margin: 0;
}
.tutor-whats-new .tutor-section-wrapper {
	display: flex;
	flex-direction: column;
	gap: 56px;
}
.tutor-whats-new .tutor-section-card {
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	border-radius: 20px;
	background-color: rgb(245, 243, 241);
	overflow: hidden;
}
.tutor-whats-new .tutor-section-card-icon {
	padding: 32px 32px 16px 32px;
}
.tutor-whats-new .tutor-section-card-title {
	display: flex;
	flex-direction: column;
	padding: 32px;
	gap: 10px;
}
.tutor-whats-new .tutor-section-card-title h6 {
	font-size: 1.25rem;
	line-height: 1.75rem;
	letter-spacing: -0.005em;
	font-weight: 500;
	color: rgb(15, 15, 15);
	margin: 0;
}
.tutor-whats-new .tutor-section-card-title p {
	margin: 0px;
	font-size: 1rem;
	line-height: 1.375rem;
	letter-spacing: 0em;
	font-weight: 400;
	color: rgba(0, 0, 0, 0.68);
}
.tutor-whats-new .tutor-section-card-image {
	display: flex;
	position: relative;
	overflow: hidden;
}
.tutor-whats-new .tutor-section-card-image img {
	width: 100%;
	height: auto;
	transition: opacity 0.5s ease-in-out;
}
.tutor-whats-new .tutor-section-card-image .tutor-img-kids {
	position: absolute;
	top: 0;
	left: 0;
	opacity: 0;
}
.tutor-whats-new .tutor-section-card-image.show-kids .tutor-img-default {
	opacity: 0;
}
.tutor-whats-new .tutor-section-card-image.show-kids .tutor-img-kids {
	opacity: 1;
}
.tutor-whats-new .tutor-section-cards {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
	gap: 16px;
}
.tutor-whats-new .tutor-section-action {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 8px 18px;
	font-size: 1rem;
	line-height: 1.375rem;
	font-weight: 500;
	color: rgb(255, 255, 255);
	border-radius: 50px;
	max-width: 196px;
	width: 100%;
	text-decoration: none;
	background-color: rgb(15, 15, 15);
	transition: all 0.2s ease;
	gap: 6px;
	cursor: pointer;
}
.tutor-whats-new .tutor-section-action svg {
	flex-shrink: 0;
}
.tutor-whats-new .tutor-section-action:hover, .tutor-whats-new .tutor-section-action:focus {
	background-color: rgba(15, 15, 15, 0.8);
}
.tutor-whats-new .tutor-section-action-outline {
	background-color: transparent;
	border: 1px solid rgb(217, 217, 217);
	color: rgb(255, 255, 255);
}
.tutor-whats-new .tutor-section-action-outline:hover, .tutor-whats-new .tutor-section-action-outline:focus {
	background-color: rgb(217, 217, 217);
	color: rgb(15, 15, 15);
}
.tutor-whats-new .tutor-learning-mode-button {
	display: block;
	position: relative;
	border: none;
	background-color: rgb(255, 255, 255);
	cursor: pointer;
	padding: 5px;
	width: 48px;
	height: 48px;
	aspect-ratio: 1/1;
	filter: grayscale(1);
	opacity: 0.6;
	border-radius: 8px;
	transition: filter 0.3s ease, opacity 0.3s ease;
}
.tutor-whats-new .tutor-learning-mode-button .progress-border {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	pointer-events: none;
}
.tutor-whats-new .tutor-learning-mode-button .progress-border rect {
	fill: none;
	stroke: rgb(2, 101, 225);
	stroke-width: 2px;
	stroke-dasharray: 184;
	stroke-dashoffset: 184;
}
.tutor-whats-new .tutor-learning-mode-button.active {
	filter: grayscale(0);
	opacity: 1;
}
.tutor-whats-new .tutor-learning-mode-button.active .progress-border rect {
	animation: buttonBorderProgress 3s linear forwards;
}
@keyframes buttonBorderProgress {
	0% {
	stroke-dashoffset: 184;
	}
	100% {
	stroke-dashoffset: 0;
	}
}
.tutor-whats-new .tutor-lm-text-transition {
	transition: opacity 0.3s ease;
}
.tutor-whats-new .tutor-lm-text-transition.fading {
	opacity: 0;
}
.tutor-whats-new .tutor-section-dashboard .tutor-section-cards .tutor-section-card-navigation {
	background-image: url("https://tutor-lms.s3.us-east-1.amazonaws.com/whats-new/navigation-bg.webp");
	background-size: cover;
}
.tutor-whats-new .tutor-section-interactive .tutor-section-cards {
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	grid-template-areas: "ordering ordering image-marking image-marking" "graph puzzle range pin";
}
.tutor-whats-new .tutor-section-interactive .tutor-section-cards .tutor-section-card-ordering {
	background-image: url("https://tutor-lms.s3.us-east-1.amazonaws.com/whats-new/ordering-bg.webp");
	background-size: cover;
}
.tutor-whats-new .tutor-section-native .tutor-section-cards {
	grid-template-areas: "native-app navigation" "mode mode";
}
.tutor-whats-new .tutor-section-native .tutor-section-cards .tutor-section-card-learning-mode {
	border: 8px solid rgb(247, 247, 247);
	padding: 8px;
	flex-direction: row;
	gap: 8px;
	justify-content: flex-start;
}
.tutor-whats-new .tutor-section-native .tutor-section-cards .tutor-section-card-learning-mode .tutor-section-card-title {
	justify-content: center;
	padding: 0;
}
.tutor-whats-new .tutor-section-native .tutor-section-cards .tutor-section-card-learning-mode .tutor-section-card-title h6 {
	font-size: 1rem;
	line-height: 1.375rem;
	letter-spacing: 0em;
	font-weight: 600;
	color: rgb(15, 15, 15);
}
.tutor-whats-new .tutor-section-native .tutor-section-cards .tutor-section-card-learning-mode .tutor-section-card-title p {
	margin: 0;
	font-size: 0.875rem;
	line-height: 1.125rem;
	letter-spacing: 0;
	font-weight: 400;
	color: rgba(0, 0, 0, 0.68);
}
.tutor-whats-new .tutor-section-a11y .tutor-section-cards {
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	grid-template-areas: "mode-preference mode-preference mode-preference mode-preference" "font contrast vision motion";
}
.tutor-whats-new .tutor-section-a11y .tutor-section-cards .tutor-section-feature-card {
	justify-content: flex-start;
}
.tutor-whats-new .tutor-section-a11y .tutor-section-card-comparison {
	display: flex;
	margin: 23px 82px 0 82px;
	border-radius: 12px 12px 0 0;
	border: 4px solid rgba(0, 0, 0, 0.03);
	border-bottom: none;
	position: relative;
	line-height: 0;
	z-index: 1;
}
.tutor-whats-new .tutor-section-a11y .tutor-section-card-comparison::before {
	content: "";
	position: absolute;
	z-index: -1;
	pointer-events: none;
	width: 241px;
	height: 544px;
	left: -236.5px;
	top: 0px;
	background: linear-gradient(347.31deg, rgba(245, 243, 241, 0.2) 37.95%, rgba(170, 170, 175, 0.12) 81.38%);
	filter: blur(1.96px);
	transform: matrix(-1, 0, 0, 1, 0, 0) translateZ(-1px);
	clip-path: polygon(0 0, 100% 100%, 0 100%);
}
.tutor-whats-new .tutor-section-a11y .tutor-section-card-comparison .tutor-comparison-inner {
	position: relative;
	width: 100%;
	overflow: hidden;
	border-radius: 8px 8px 0 0;
	line-height: 0;
}
.tutor-whats-new .tutor-section-a11y .tutor-section-card-comparison .tutor-comparison-inner img {
	width: 100%;
	height: auto;
	object-fit: cover;
	display: block;
	user-select: none;
	pointer-events: none;
}
.tutor-whats-new .tutor-section-a11y .tutor-section-card-comparison .tutor-comparison-inner .tutor-comparison-img-light-wrapper {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	clip-path: polygon(0 0, var(--pos) 0, var(--pos) 100%, 0 100%);
}
.tutor-whats-new .tutor-section-a11y .tutor-section-card-comparison .tutor-comparison-inner .tutor-comparison-handle-line {
	position: absolute;
	top: 0;
	bottom: 0;
	left: var(--pos);
	width: 2px;
	background-color: rgb(0, 98, 254);
	transform: translateX(-50%);
	pointer-events: none;
	z-index: 5;
}
.tutor-whats-new .tutor-section-a11y .tutor-section-card-comparison .tutor-comparison-slider {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	opacity: 0;
	cursor: ew-resize;
	margin: 0;
	z-index: 10;
	-webkit-appearance: none;
	appearance: none;
}
.tutor-whats-new .tutor-section-a11y .tutor-section-card-comparison .tutor-comparison-handle-icon {
	position: absolute;
	top: 50%;
	left: var(--pos);
	transform: translate(-50%, -50%);
	pointer-events: none;
	z-index: 6;
	width: 38px;
	height: 38px;
	aspect-ratio: 1/1;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: 50%;
	background: white;
	box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.3), 0 4px 16px rgba(0, 0, 0, 0.15);
}
.tutor-whats-new .tutor-section-a11y .tutor-section-card-comparison .tutor-comparison-handle-icon svg {
	position: relative;
	z-index: 1;
}
.tutor-whats-new .tutor-section-instructor-dashboard .tutor-section-cards {
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	grid-template-areas: "learners assignments announcements";
}

@media (max-width: 1024px) {
	#wpbody-content {
	padding-bottom: 0;
	}
	.tutor-whats-new br {
	display: none;
	}
	.tutor-whats-new .tutor-section-layout {
	padding: 48px 20px;
	gap: 96px;
	}
	.tutor-whats-new .tutor-section-wrapper {
	gap: 40px;
	}
	.tutor-whats-new .tutor-section-title {
	gap: 10px;
	align-items: flex-start;
	}
	.tutor-whats-new .tutor-section-title-left, .tutor-whats-new .tutor-section-title-center {
	gap: 4px;
	}
	.tutor-whats-new .tutor-section-title-left h2, .tutor-whats-new .tutor-section-title-center h2 {
	font-size: 2rem;
	line-height: 2.5rem;
	margin-inline-end: 10px;
	}
	.tutor-whats-new .tutor-section-title-left p, .tutor-whats-new .tutor-section-title-center p {
	font-size: 0.75rem;
	line-height: 1.125rem;
	}
	.tutor-whats-new .tutor-section-action {
	max-width: fit-content;
	}
	.tutor-whats-new .tutor-section-cards {
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 10px;
	}
	.tutor-whats-new .tutor-section-card-title {
	gap: 10px;
	padding: 20px;
	}
	.tutor-whats-new .tutor-section-interactive .tutor-section-cards {
	grid-template-areas: "ordering image-marking" "graph puzzle" "range pin";
	}
	.tutor-whats-new .tutor-section-a11y .tutor-section-cards {
	grid-template-areas: "mode-preference mode-preference" "font contrast" "vision motion";
	}
	.tutor-whats-new .tutor-section-a11y .tutor-section-cards .tutor-section-card-comparison {
	margin: 0 40px 0 40px;
	}
	.tutor-whats-new .tutor-section-instructor-dashboard .tutor-section-cards {
	grid-template-areas: "learners assignments" "announcements announcements";
	}
	.tutor-whats-new .tutor-section-instructor-dashboard .tutor-section-cards [style*="grid-area: announcements"] {
	height: 400px;
	}
	.tutor-whats-new .tutor-section-instructor-dashboard .tutor-section-cards [style*="grid-area: announcements"] img {
	width: min-content;
	margin-inline: auto;
	}
}
@media (max-width: 768px) {
	.tutor-whats-new {
	margin-left: -10px;
	}
	.tutor-whats-new .tutor-hero-image {
	height: 404px;
	object-fit: cover;
	}
}
@media (max-width: 430px) {
	.tutor-whats-new .tutor-hero-image {
	height: 263px;
	object-fit: cover;
	}
	.tutor-whats-new .tutor-whats-new-dismiss-bar {
	top: 8px;
	
	a {
		margin-inline-end: 8px;
	}
	}
	.tutor-whats-new .tutor-whats-new-dismiss-btn {
	border-radius: 999px;
	padding: 12px;
	gap: 0;
	}
	.tutor-whats-new .tutor-whats-new-dismiss-btn span {
	display: none;
	}
	.tutor-whats-new .tutor-whats-new-dismiss-btn svg {
	width: 20px;
	height: 20px;
	}
	.tutor-whats-new .tutor-section-layout {
	padding-inline: 16px;
	}
	.tutor-whats-new .tutor-section-cards {
	gap: 16px;
	}
	.tutor-whats-new .tutor-section-title {
	gap: 10px;
	flex-wrap: wrap;
	}
	.tutor-whats-new .tutor-section-title-left, .tutor-whats-new .tutor-section-title-center, .tutor-whats-new .tutor-section-title-right {
	flex-basis: 100%;
	}
	.tutor-whats-new .tutor-section-action {
	max-width: 196px;
	}
	.tutor-whats-new .tutor-section-interactive .tutor-section-cards {
	grid-template-areas: "ordering" "image-marking" "graph" "puzzle" "range" "pin";
	}
	.tutor-whats-new .tutor-section-native .tutor-section-cards {
	grid-template-areas: "native-app" "navigation" "mode";
	}
	.tutor-whats-new .tutor-section-a11y .tutor-section-cards {
	grid-template-areas: "mode-preference" "font" "contrast" "vision" "motion";
	}
	.tutor-whats-new .tutor-section-a11y .tutor-section-cards .tutor-section-card-comparison {
	margin: 0 20px 0 20px;
	}
	.tutor-whats-new .tutor-section-instructor-dashboard .tutor-section-cards {
	grid-template-areas: "learners" "assignments" "announcements";
	}
}

</style>

<div class="tutor-whats-new">
	<div class="tutor-whats-new-dismiss-bar">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=tutor' ) ); ?>" class="tutor-section-action tutor-section-action-outline tutor-whats-new-dismiss-btn">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m12.4 3.6-8.8 8.8m0-8.8 8.8 8.8"/></svg>
			<span><?php esc_html_e( 'Close', 'tutor' ); ?></span>
		</a>
	</div>
	<img class="tutor-hero-image" src="<?php echo esc_url( $asset_base . 'whats-new-hero.webp' ); ?>" alt="<?php esc_attr_e( "What's new in v4", 'tutor' ); ?>">

	<div class="tutor-section-layout">

		<!-- Dashboard and Navigation -->
		<section class="tutor-section-wrapper tutor-section-dashboard">
			<div class="tutor-section-title">
				<div class="tutor-section-title-left">
					<p><?php esc_html_e( 'Dashboard and Navigation', 'tutor' ); ?></p>
					<h2><?php esc_html_e( 'Designed for flow.', 'tutor' ); ?></h2>
				</div>
				<div class="tutor-section-title-right">
					<p><?php esc_html_e( 'Every screen, every interaction, every detail has been reconsidered, so students can focus on learning instead of figuring out where to go next.', 'tutor' ); ?></p>
				</div>
			</div>

			<div class="tutor-section-cards">
				<?php foreach ( $dashboard_cards as $card ) : ?>
					<?php $render_card( $card ); ?>
				<?php endforeach; ?>
			</div>
		</section>

		<!-- Learners First -->
		<section class="tutor-section-wrapper tutor-section-learner">
			<div class="tutor-section-title">
				<div class="tutor-section-title-left">
					<p><?php esc_html_e( 'Learner first design', 'tutor' ); ?></p>
					<h2><?php esc_html_e( 'Everything they need. Exactly where they expect it.', 'tutor' ); ?></h2>
				</div>
				<div class="tutor-section-title-right">
					<p><?php esc_html_e( 'Notes, discussions, resources, lesson comments – all accessible without leaving the lesson. No more tab-switching. No more hunting.', 'tutor' ); ?></p>
					<div>
						<?php $render_action_button( $action_button_text, $action_button_url ); ?>
					</div>
				</div>
			</div>

			<div class="tutor-section-cards">
				<?php foreach ( $learner_cards as $card ) : ?>
					<?php $render_card( $card ); ?>
				<?php endforeach; ?>
			</div>
		</section>

		<!-- Interactive Assessments -->
		<section class="tutor-section-wrapper tutor-section-interactive">
			<div class="tutor-section-title">
				<div class="tutor-section-title-left">
					<p><?php esc_html_e( 'Interactive Assessments', 'tutor' ); ?></p>
					<h2><?php esc_html_e( '5 new ways to make assessment actually fun.', 'tutor' ); ?></h2>
				</div>
				<div class="tutor-section-title-right">
					<p><?php esc_html_e( 'Most LMSs treat assessment as the boring part. Tutor LMS 4.0 turns it into the part students look forward to — with five new interactive quiz types designed to keep them engaged.', 'tutor' ); ?></p>
					<div>
						<?php $render_action_button( $action_button_text, $action_button_url ); ?>
					</div>
				</div>
			</div>

			<div class="tutor-section-cards">
				<?php foreach ( $interactive_cards as $card ) : ?>
					<?php $render_card( $card ); ?>
				<?php endforeach; ?>
			</div>
		</section>

		<!-- Native App -->
		<section class="tutor-section-wrapper tutor-section-native">
			<div class="tutor-section-title">
				<div class="tutor-section-title-center">
					<p><?php esc_html_e( 'Native App Like Experience', 'tutor' ); ?></p>
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
				<div class="tutor-section-card" style="grid-area: native-app;">
					<div class="tutor-section-card-title">
						<h6><?php esc_html_e( 'Native app like experience', 'tutor' ); ?></h6>
						<p><?php esc_html_e( 'The mobile interface mirrors a polished native app — smooth transitions, touch-friendly controls, and a layout built for small screens without any compromise.', 'tutor' ); ?></p>
					</div>
					<div class="tutor-section-card-image tutor-toggle-images">
						<img class="tutor-img-default" src="<?php echo esc_url( $asset_base . 'native-app.webp' ); ?>" alt="<?php esc_attr_e( 'Native app like experience', 'tutor' ); ?>">
						<img class="tutor-img-kids" src="<?php echo esc_url( $asset_base . 'native-app-kids.webp' ); ?>" alt="<?php esc_attr_e( 'Native app like experience (Kids)', 'tutor' ); ?>">
					</div>
				</div>

				<div class="tutor-section-card" style="grid-area: navigation;">
					<div class="tutor-section-card-title">
						<h6><?php esc_html_e( 'Smoother navigation', 'tutor' ); ?></h6>
						<p><?php esc_html_e( 'Students move between courses, lessons, and their profile without friction — every tap takes them exactly where they need to go.', 'tutor' ); ?></p>
					</div>
					<div class="tutor-section-card-image tutor-toggle-images">
						<img class="tutor-img-default" src="<?php echo esc_url( $asset_base . 'smooth-navigation.webp' ); ?>" alt="<?php esc_attr_e( 'Smoother navigation', 'tutor' ); ?>">
						<img class="tutor-img-kids" src="<?php echo esc_url( $asset_base . 'smooth-navigation-kids.webp' ); ?>" alt="<?php esc_attr_e( 'Smoother navigation (Kids)', 'tutor' ); ?>">
					</div>
				</div>

				<div class="tutor-section-card tutor-section-card-learning-mode" style="grid-area: mode;">
					<button class="tutor-learning-mode-button active" data-mode="default" aria-label="<?php esc_attr_e( 'Switch to modern mode', 'tutor' ); ?>">
						<svg class="progress-border" viewBox="0 0 48 48" aria-hidden="true" focusable="false"><rect x="1" y="1" width="46" height="46" rx="7"></rect></svg>
						<svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="none" viewBox="0 0 38 38" aria-hidden="true" focusable="false"><path fill="#0265e1" d="M27.559 22.623c.745 0 1.35.605 1.35 1.35v2.709a1.35 1.35 0 0 1-1.35 1.35H11.35A1.35 1.35 0 0 1 10 26.682v-2.708c0-.746.605-1.35 1.35-1.351zm.137-4.059a1.213 1.213 0 0 1 0 2.425H11.212a1.213 1.213 0 0 1 0-2.425zM27.56 10c.745 0 1.35.605 1.35 1.35v4.502a1.35 1.35 0 0 1-1.35 1.351H11.35A1.35 1.35 0 0 1 10 15.853V11.35c0-.746.605-1.35 1.35-1.351z"/><path fill="#fcfdff" d="M13.56 23.57c.373 0 .676.303.676.676v2.242a.676.676 0 0 1-.676.675h-2.148a.676.676 0 0 1-.676-.675v-2.242c0-.373.303-.675.676-.675zM12.556 12.28a.45.45 0 0 1 .696 0l2.731 3.325 2.01-2.538a.45.45 0 0 1 .706 0l3.284 4.15H11.358c-1.14 0-1.768-1.327-1.044-2.209zm14.554-1.364a.9.9 0 1 1 0 1.8.9.9 0 0 1 0-1.8"/></svg>
					</button>
					<button class="tutor-learning-mode-button" data-mode="kids" aria-label="<?php esc_attr_e( 'Switch to kids mode', 'tutor' ); ?>">
						<svg class="progress-border" viewBox="0 0 48 48" aria-hidden="true" focusable="false"><rect x="1" y="1" width="46" height="46" rx="7"></rect></svg>
						<svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="none" viewBox="0 0 38 38" aria-hidden="true" focusable="false"><g><rect width="20.561" height="7.833" x="9.672" y="9" fill="#ffe129" rx="1.201"/><g filter="url(#a)"><path fill="#58cd04" d="M9.672 19.212a.9.9 0 0 1 .9-.9h18.76a.9.9 0 0 1 .9.9v.836a.9.9 0 0 1-.9.9h-18.76a.9.9 0 0 1-.9-.9z"/></g><g filter="url(#b)"><rect width="20.561" height="5.882" x="9.672" y="22.725" fill="#51be02" rx="1.801"/></g><rect width="4.414" height="4.53" x="10.613" y="23.399" fill="#fff" rx="2.207"/><path fill="#fc7501" d="M24.163 11.155c.077 0 .139.061.139.138a4.346 4.346 0 0 1-8.69 0 .138.138 0 1 1 .277 0 4.068 4.068 0 0 0 8.134 0 .14.14 0 0 1 .14-.138m-4.887-.63a.14.14 0 0 1 .14.138c0 .234-.131.432-.318.568a1.2 1.2 0 0 1-.71.214c-.27 0-.522-.079-.71-.214-.186-.136-.317-.334-.317-.568a.139.139 0 1 1 .278 0c0 .122.068.245.203.343a.94.94 0 0 0 .547.161.94.94 0 0 0 .546-.161c.134-.098.203-.22.203-.343 0-.077.062-.138.138-.138m1.16-.072a.14.14 0 0 1 .132.146c-.006.121.057.248.186.351.13.104.32.178.539.189a.94.94 0 0 0 .553-.135c.139-.09.213-.21.22-.332a.14.14 0 0 1 .278.014c-.012.233-.153.425-.346.55a1.2 1.2 0 0 1-.719.18 1.2 1.2 0 0 1-.699-.249c-.18-.144-.3-.349-.289-.582a.14.14 0 0 1 .145-.132"/><path fill="#ff6ccb" d="M31.416 18.473c-.505.936 0 1.852.325 2.604.114.265.227.517.42.621s.464.06.749.01c.807-.14 1.85-.222 2.355-1.158s-.196-2.17-1.547-1.738c-.38-1.367-1.797-1.275-2.302-.339M5.196 21.36a.3.3 0 0 0-.035-.164l-.848-1.573a.3.3 0 0 1 .256-.443l1.783-.048a.3.3 0 0 0 .16-.051l1.5-1.013a.3.3 0 0 1 .466.27l-.128 1.808a.3.3 0 0 0 .035.164l.848 1.573a.3.3 0 0 1-.255.443l-1.784.048a.3.3 0 0 0-.16.05l-1.5 1.014a.3.3 0 0 1-.466-.271z"/></g><defs><filter id="a" width="20.561" height="2.786" x="9.672" y="18.312" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/><feOffset dy=".15"/><feComposite in2="hardAlpha" operator="out"/><feColorMatrix values="0 0 0 0 0.231373 0 0 0 0 0.54902 0 0 0 0 0 0 0 0 1 0"/><feBlend in2="BackgroundImageFix" result="effect1_dropShadow_21950_196354"/><feBlend in="SourceGraphic" in2="effect1_dropShadow_21950_196354" result="shape"/></filter><filter id="b" width="20.561" height="6.182" x="9.672" y="22.725" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/><feOffset dy=".3"/><feComposite in2="hardAlpha" operator="out"/><feColorMatrix values="0 0 0 0 0.231373 0 0 0 0 0.54902 0 0 0 0 0 0 0 0 1 0"/><feBlend in2="BackgroundImageFix" result="effect1_dropShadow_21950_196354"/><feBlend in="SourceGraphic" in2="effect1_dropShadow_21950_196354" result="shape"/></filter></defs></svg>
					</button>

					<div class="tutor-section-card-title tutor-lm-text-transition"
						data-default-title="<?php esc_attr_e( 'Modern Modes', 'tutor' ); ?>"
						data-default-desc="<?php esc_attr_e( 'A modern, distraction-free learning experience.', 'tutor' ); ?>"
						data-kids-title="<?php esc_attr_e( 'Kids Mode', 'tutor' ); ?>"
						data-kids-desc="<?php esc_attr_e( 'A fun, engaging and distraction-free learning experience.', 'tutor' ); ?>">
						<h6><?php esc_html_e( 'Modern Modes', 'tutor' ); ?></h6>
						<p><?php esc_html_e( 'A modern, distraction-free learning experience.', 'tutor' ); ?></p>
					</div>
				</div>
			</div>
		</section>

		<!-- Accessibility -->
		<section class="tutor-section-wrapper tutor-section-a11y">
			<div class="tutor-section-title">
				<div class="tutor-section-title-center">
					<p><?php esc_html_e( 'Accessibility', 'tutor' ); ?></p>
					<h2>
						<?php
						printf(
							// translators: placeholder is a line break.
							esc_html__( 'No learner left behind. %s By design.', 'tutor' ),
							'<br/>'
						);
						?>
					</h2>
					<p><?php esc_html_e( 'From adjustable font sizes to color-blind friendly filters and reduced motion, Tutor LMS 4.0 adapts to how each student learns best — not the other way around.', 'tutor' ); ?></p>
				</div>
			</div>

			<div class="tutor-section-cards">
				<!-- Mode with preference -->
				<div class="tutor-section-card" style="grid-area: mode-preference;">
					<div class="tutor-section-card-title">
						<h6><?php esc_html_e( 'Mode with preference', 'tutor' ); ?></h6>
						<p><?php esc_html_e( 'Students can switch between Modern, Dark, and Kids modes to match their personal comfort and learning environment.', 'tutor' ); ?></p>
					</div>

					<div class="tutor-section-card-comparison" data-comparison-pos="50">
						<div class="tutor-comparison-inner">
							<img src="<?php echo esc_url( $asset_base . 'dashboard-dark.webp' ); ?>" alt="<?php esc_attr_e( 'Dark Mode', 'tutor' ); ?>">
							<div class="tutor-comparison-img-light-wrapper">
								<img src="<?php echo esc_url( $asset_base . 'dashboard-light.webp' ); ?>" alt="<?php esc_attr_e( 'Light Mode', 'tutor' ); ?>">
							</div>
							<div class="tutor-comparison-handle-line"></div>
						</div>
						<input type="range" min="0" max="100" value="50" class="tutor-comparison-slider" aria-label="<?php esc_attr_e( 'Compare light and dark mode', 'tutor' ); ?>">
						<div class="tutor-comparison-handle-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="19" height="9" fill="none" viewBox="0 0 19 9" aria-hidden="true" focusable="false"><path fill="#0f0f0f" d="M5.429 7.982V.703q0-.298-.161-.501Q5.108 0 4.893 0a.476.476 0 0 0-.281.105l-4.37 3.64a.66.66 0 0 0-.182.264.95.95 0 0 0 0 .668q.06.157.181.264l4.37 3.64a.5.5 0 0 0 .14.079.4.4 0 0 0 .142.026q.214 0 .375-.203a.78.78 0 0 0 .16-.5M13.571 7.982V.703q0-.298.161-.501.16-.202.375-.202.067 0 .142.026t.14.08l4.37 3.639q.12.105.18.264a.95.95 0 0 1 0 .668.64.64 0 0 1-.18.264l-4.37 3.64a.5.5 0 0 1-.14.079.4.4 0 0 1-.142.026q-.214 0-.375-.203a.78.78 0 0 1-.16-.5"/></svg>
						</div>
					</div>
				</div>

				<?php foreach ( $a11y_feature_cards as $card ) : ?>
					<?php $render_card( $card ); ?>
				<?php endforeach; ?>
			</div>
		</section>

		<!-- Instructor Dashboard -->
		<section class="tutor-section-wrapper tutor-section-instructor-dashboard">
			<div class="tutor-section-title">
				<div class="tutor-section-title-left">
					<p><?php esc_html_e( 'Instructor Dashboard', 'tutor' ); ?></p>
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
					<p><?php esc_html_e( 'Quiz attempts, assignments, revenue, student progress — everything tracked, everything visible, without jumping between dashboards. Spend less time administrating and more time doing what you\'re actually good at.', 'tutor' ); ?></p>
					<div>
						<?php $render_action_button( $action_button_text, $action_button_url ); ?>
					</div>
				</div>
			</div>

			<div class="tutor-section-cards">
				<?php foreach ( $instructor_cards as $card ) : ?>
					<?php $render_card( $card ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	initLearningModeToggle();
	initComparisonSlider();

	function initLearningModeToggle() {
		const modeButtons = Array.from(document.querySelectorAll('.tutor-learning-mode-button'));
		const toggleImages = document.querySelectorAll('.tutor-toggle-images');
		const textContainer = document.querySelector('.tutor-lm-text-transition');

		if (!modeButtons.length || !textContainer) return;

		const titleEl = textContainer.querySelector('h6');
		const descEl = textContainer.querySelector('p');
		const MODE_SWITCH_DELAY_MS = 300;

		const switchMode = (mode) => {
			modeButtons.forEach((btn) => {
				btn.classList.remove('active');
				// Force reflow to reset the progress-border animation.
				void btn.offsetWidth;
			});

			const activeBtn = modeButtons.find((btn) => btn.dataset.mode === mode);
			if (activeBtn) activeBtn.classList.add('active');

			toggleImages.forEach((container) => {
				container.classList.toggle('show-kids', mode === 'kids');
			});

			textContainer.classList.add('fading');
			setTimeout(() => {
				const titleAttr = mode === 'kids' ? 'kidsTitle' : 'defaultTitle';
				const descAttr = mode === 'kids' ? 'kidsDesc' : 'defaultDesc';

				if (titleEl) titleEl.textContent = textContainer.dataset[titleAttr] || '';
				if (descEl) descEl.textContent = textContainer.dataset[descAttr] || '';
				textContainer.classList.remove('fading');
			}, MODE_SWITCH_DELAY_MS);
		};

		modeButtons.forEach((btn) => {
			btn.addEventListener('click', () => {
				const mode = btn.dataset.mode;
				if (mode) switchMode(mode);
			});

			const rect = btn.querySelector('.progress-border rect');
			if (rect) {
				rect.addEventListener('animationend', () => {
					if (btn.classList.contains('active')) {
						const nextMode = btn.dataset.mode === 'default' ? 'kids' : 'default';
						switchMode(nextMode);
					}
				});
			}
		});
	}

	function initComparisonSlider() {
		const container = document.querySelector('.tutor-section-card-comparison');
		const slider = container ? container.querySelector('.tutor-comparison-slider') : null;

		if (!container || !slider) return;

		const initialPos = Number(container.dataset.comparisonPos) || Number(slider.value) || 50;
		container.style.setProperty('--pos', initialPos + '%');
		slider.value = String(initialPos);

		slider.addEventListener('input', () => {
			container.style.setProperty('--pos', slider.value + '%');
		});
	}
});
</script>