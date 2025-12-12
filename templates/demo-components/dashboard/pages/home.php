<?php
/**
 * Tutor dashboard.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

// Define stat card variations with sample data matching the design.
$stat_cards = array(
	array(
		'variation' => 'brand',
		'title'     => esc_html__( 'Enrolled Courses', 'tutor' ),
		'icon'      => Icon::COURSES,
		'value'     => '12',
		'change'    => '+2',
	),
	array(
		'variation' => 'exception1',
		'title'     => esc_html__( 'Active', 'tutor' ),
		'icon'      => Icon::PLAY_LINE,
		'value'     => '3',
		'change'    => '+2',
	),
	array(
		'variation' => 'success',
		'title'     => esc_html__( 'Completed', 'tutor' ),
		'icon'      => Icon::COMPLETED_CIRCLE,
		'value'     => '5',
		'change'    => '+2',
	),
	array(
		'variation' => 'exception4',
		'title'     => esc_html__( 'Time Spent', 'tutor' ),
		'icon'      => Icon::TIME,
		'value'     => '375h+',
		'change'    => '+2',
	),
);

// Define popup progress card with sample data.
$popup_progress_card = array(
	'card_title'  => esc_html__( 'Amazing!', 'tutor' ),
	'user_name'   => '',
	'subtitle'    => esc_html__( "You've dedicated over", 'tutor' ),
	'value'       => '375h+',
	'button_text' => esc_html__( "I'm Happy", 'tutor' ),
	'breakdown'   => sprintf(
		/* translators: %1$s: streak days */
		__( "Amazing dedication! You've maintained a <strong>%1\$s-day learning streak</strong> and reached a new milestone!", 'tutor' ),
		'15'
	),
);

// Sample progress cards data.
$progress_cards = array(
	array(
		'image_url'         => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=400&fit=crop',
		'category'          => esc_html__( 'Preschool', 'tutor' ),
		'course_title'      => esc_html__( 'Drawing for Beginners Level -2', 'tutor' ),
		'lessons_completed' => 5,
		'lessons_total'     => 9,
		'progress_percent'  => 60,
	),
	array(
		'image_url'         => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=400&h=400&fit=crop',
		'category'          => esc_html__( 'Elementary', 'tutor' ),
		'course_title'      => esc_html__( 'Advanced Mathematics', 'tutor' ),
		'lessons_completed' => 8,
		'lessons_total'     => 12,
		'progress_percent'  => 67,
	),
);

// Sample upcoming lesson card data.
$upcoming_lessons = array(
	array(
		'date_text'     => esc_html__( 'Today', 'tutor' ),
		'time_text'     => '2:00 PM',
		'lesson_title'  => esc_html__( 'This is the lesson title', 'tutor' ),
		'course_name'   => esc_html__( 'Camera Skills & Photo Theory', 'tutor' ),
		'show_live_tag' => true,
	),
	array(
		'date_text'     => esc_html__( 'Tomorrow', 'tutor' ),
		'time_text'     => '10:00 AM',
		'lesson_title'  => esc_html__( 'Advanced Photography Techniques', 'tutor' ),
		'course_name'   => esc_html__( 'Camera Skills & Photo Theory', 'tutor' ),
		'show_live_tag' => true,
	),
	array(
		'date_text'     => esc_html__( 'Dec 15', 'tutor' ),
		'time_text'     => '3:30 PM',
		'lesson_title'  => esc_html__( 'Introduction to Video Editing', 'tutor' ),
		'course_name'   => esc_html__( 'Digital Media Production', 'tutor' ),
		'show_live_tag' => true,
	),
	array(
		'date_text'     => esc_html__( 'Dec 16', 'tutor' ),
		'time_text'     => '11:00 AM',
		'lesson_title'  => esc_html__( 'Color Grading Fundamentals', 'tutor' ),
		'course_name'   => esc_html__( 'Digital Media Production', 'tutor' ),
		'show_live_tag' => true,
	),
);

?>
<?php tutor_load_template( 'demo-components.dashboard.pages.instructor.home' ); ?>
<?php tutor_load_template( 'demo-components.dashboard.components.announcement-notice' ); ?>
<div class="tutor-text-h3 tutor-color-black tutor-mb-6">
	<?php esc_html_e( 'Welcome to TutorLMS Home', 'tutor' ); ?>
</div>

<div class="tutor-mb-8">
	<div class="tutor-flex tutor-gap-4">
		<?php foreach ( $stat_cards as $card ) : ?>
			<div class="tutor-flex-1">
			<?php
			tutor_load_template(
				'demo-components.dashboard.components.stat-card',
				array(
					'variation'  => isset( $card['variation'] ) ? $card['variation'] : 'enrolled',
					'card_title' => isset( $card['title'] ) ? $card['title'] : '',
					'icon'       => isset( $card['icon'] ) ? $card['icon'] : '',
					'value'      => isset( $card['value'] ) ? $card['value'] : '',
					'change'     => isset( $card['change'] ) ? $card['change'] : '',
				)
			);
			?>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<div class="tutor-mb-8">
	<h2 class="tutor-text-xl tutor-font-bold tutor-mb-6"><?php echo esc_html__( 'My Progress', 'tutor' ); ?></h2>
	<div class="tutor-flex tutor-flex-column tutor-gap-4">
		<?php foreach ( $progress_cards as $card ) : ?>
			<?php
			tutor_load_template(
				'demo-components.dashboard.components.progress-card',
				array(
					'image_url'         => isset( $card['image_url'] ) ? $card['image_url'] : '',
					'category'          => isset( $card['category'] ) ? $card['category'] : '',
					'course_title'      => isset( $card['course_title'] ) ? $card['course_title'] : '',
					'lessons_completed' => isset( $card['lessons_completed'] ) ? $card['lessons_completed'] : 0,
					'lessons_total'     => isset( $card['lessons_total'] ) ? $card['lessons_total'] : 0,
					'progress_percent'  => isset( $card['progress_percent'] ) ? $card['progress_percent'] : 0,
				)
			);
			?>
		<?php endforeach; ?>
	</div>
</div>

<div class="tutor-mb-8">
	<h2 class="tutor-text-xl tutor-font-bold tutor-mb-6"><?php echo esc_html__( 'Upcoming Lessons', 'tutor' ); ?></h2>
	<div class="tutor-grid tutor-grid-cols-2 tutor-gap-4">
		<?php
		foreach ( $upcoming_lessons as $lesson ) :
			tutor_load_template(
				'core-components.upcoming-lesson-card',
				array(
					'date_text'     => isset( $lesson['date_text'] ) ? $lesson['date_text'] : '',
					'time_text'     => isset( $lesson['time_text'] ) ? $lesson['time_text'] : '',
					'lesson_title'  => isset( $lesson['lesson_title'] ) ? $lesson['lesson_title'] : '',
					'course_name'   => isset( $lesson['course_name'] ) ? $lesson['course_name'] : '',
					'show_live_tag' => isset( $lesson['show_live_tag'] ) ? $lesson['show_live_tag'] : true,
				)
			);
		endforeach;
		?>
	</div>
</div>

<div class="tutor-mb-6">
	<div class="tutor-mb-8">
		<h2 class="tutor-text-xl tutor-font-bold tutor-mb-6"><?php echo esc_html__( 'Upcoming Lessons', 'tutor' ); ?></h2>
		<div class="tutor-grid tutor-grid-cols-2 tutor-gap-4">
			<?php
			tutor_load_template(
				'demo-components.dashboard.components.popup-progress-card',
				array(
					'card_title'  => isset( $popup_progress_card['card_title'] ) ? $popup_progress_card['card_title'] : esc_html__( 'Amazing!', 'tutor' ),
					'user_name'   => isset( $popup_progress_card['user_name'] ) ? $popup_progress_card['user_name'] : '',
					'subtitle'    => isset( $popup_progress_card['subtitle'] ) ? $popup_progress_card['subtitle'] : '',
					'value'       => isset( $popup_progress_card['value'] ) ? $popup_progress_card['value'] : '',
					'button_text' => isset( $popup_progress_card['button_text'] ) ? $popup_progress_card['button_text'] : esc_html__( "I'm Happy", 'tutor' ),
					'breakdown'   => isset( $popup_progress_card['breakdown'] ) ? $popup_progress_card['breakdown'] : '',
				)
			);
			?>
		</div>
	</div>
</div>
