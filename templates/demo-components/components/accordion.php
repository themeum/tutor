<?php
/**
 * Demo: Accordion Component
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Prepare items for basic accordion (multiple open).
$basic_items = array(
	array(
		'title'   => __( 'About this Course', 'tutor' ),
		'content' => '<p class="tutor-p1">' . __( 'This course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications. You will learn the fundamentals and advanced concepts through hands-on exercises and real-world examples.', 'tutor' ) . '</p>',
		'icon'    => Icon::CHEVRON_DOWN,
	),
	array(
		'title'   => __( 'Course Requirements', 'tutor' ),
		'content' => '<p class="tutor-p1">' . __( 'To get the most out of this course, you should have basic knowledge of the subject. No prior experience is required, but familiarity with the concepts will help you progress faster.', 'tutor' ) . '</p>',
		'icon'    => Icon::CHEVRON_DOWN,
	),
	array(
		'title'   => __( 'What You Will Learn', 'tutor' ),
		'content' => '<ul class="tutor-list tutor-list-disc tutor-ml-6"><li>' . __( 'Core concepts and fundamentals', 'tutor' ) . '</li><li>' . __( 'Advanced techniques and best practices', 'tutor' ) . '</li><li>' . __( 'Real-world applications and case studies', 'tutor' ) . '</li></ul>',
		'icon'    => Icon::CHEVRON_DOWN,
	),
);

// Prepare items for single accordion (only one open at a time).
$single_items = array(
	array(
		'title'   => __( 'Module 1: Introduction', 'tutor' ),
		'content' => '<p class="tutor-p1">' . __( 'This module introduces you to the basic concepts and sets the foundation for the rest of the course.', 'tutor' ) . '</p>',
		'icon'    => Icon::CHEVRON_DOWN,
	),
	array(
		'title'   => __( 'Module 2: Advanced Topics', 'tutor' ),
		'content' => '<p class="tutor-p1">' . __( 'Dive deeper into advanced topics and explore complex scenarios with detailed explanations.', 'tutor' ) . '</p>',
		'icon'    => Icon::CHEVRON_DOWN,
	),
	array(
		'title'   => __( 'Module 3: Practical Applications', 'tutor' ),
		'content' => '<p class="tutor-p1">' . __( 'Apply what you have learned through hands-on exercises and real-world projects.', 'tutor' ) . '</p>',
		'icon'    => Icon::CHEVRON_DOWN,
	),
);

// Component variables for multiple accordion (multiple open).
$component_vars_multiple = array(
	'items'        => $basic_items,
	'multiple'     => true,
	'default_open' => array( 0 ),
);

// Component variables for single accordion (only one open at a time).
$component_vars_single = array(
	'items'        => $single_items,
	'multiple'     => false,
	'default_open' => array( 0 ),
);

?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<div class="tutor-p-6 tutor-space-y-6">
		<h3 class="tutor-text-xl tutor-font-medium tutor-mb-6">
			<?php echo esc_html__( 'Accordion Component Demo', 'tutor' ); ?>
		</h3>

		<div class="tutor-space-y-6">
			<div class="tutor-space-y-3">
				<h4 class="tutor-text-base tutor-font-medium">
					<?php echo esc_html__( 'Multiple Accordion (Multiple Items Can Be Open)', 'tutor' ); ?>
				</h4>
				<p class="tutor-text-sm tutor-text-secondary tutor-mb-3">
					<?php echo esc_html__( 'In this mode, multiple accordion items can be open simultaneously.', 'tutor' ); ?>
				</p>
				<?php tutor_load_template( 'core-components.accordion', $component_vars_multiple ); ?>
			</div>

			<div class="tutor-space-y-3 tutor-mt-6">
				<h4 class="tutor-text-base tutor-font-medium">
					<?php echo esc_html__( 'Single Accordion (Only One Item Open at a Time)', 'tutor' ); ?>
				</h4>
				<p class="tutor-text-sm tutor-text-secondary tutor-mb-3">
					<?php echo esc_html__( 'In this mode, opening one item will automatically close the previously opened item.', 'tutor' ); ?>
				</p>
				<?php tutor_load_template( 'core-components.accordion', $component_vars_single ); ?>
			</div>
		</div>
	</div>
</section>