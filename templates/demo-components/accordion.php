<?php
/**
 * Demo: Accordion Component
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Prepare items for basic accordion.
$basic_items = array(
	array(
		'title'   => __( 'About this Course', 'tutor' ),
		'content' => '<p class="tutor-p1">' . __( 'This course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.', 'tutor' ) . '</p>',
		'icon'    => Icon::CHEVRON_DOWN,
	),
	array(
		'title'   => __( 'Course Requirements', 'tutor' ),
		'content' => 'This is a content of the course requirements.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.his course provides a comprehensive overview of the subject matter, covering all essential topics and practical applications.',
		'icon'    => Icon::CHEVRON_DOWN,
	),
);

// Component variables for basic accordion (multiple open).
$component_vars_basic = array(
	'items'        => $basic_items,
	'multiple'     => true,
	'default_open' => array( 0 ),
);

?>

<section class="tutor-bg-white tutor-py-6 tutor-px-8 tutor-rounded-lg tutor-shadow-sm">
	<div class="tutor-p-6 tutor-space-y-6">
		<h3 class="tutor-text-xl tutor-font-medium">
			<?php echo esc_html__( 'Accordion Component Demo', 'tutor' ); ?>
		</h3>

		<div class="tutor-space-y-3">
			<h4 class="tutor-text-base tutor-font-medium">
				<?php echo esc_html__( 'Basic Accordion (Multiple Open)', 'tutor' ); ?>
			</h4>
			<?php tutor_load_template( 'core-components.accordion', $component_vars_basic ); ?>
		</div>
	</div>
</section>