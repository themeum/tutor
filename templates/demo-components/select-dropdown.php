<?php
/**
 * Demo: Select Dropdown
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Prepare options for the component.
$dropdown_options = array(
	array(
		'label' => __( 'Option One', 'tutor' ),
		'value' => 'one',
		'icon'  => Icon::BOOK,
	),
	array(
		'label' => __( 'Option Two', 'tutor' ),
		'value' => 'two',
		'icon'  => Icon::CALENDAR,
	),
	array(
		'label'    => __( 'Disabled Option', 'tutor' ),
		'value'    => 'disabled',
		'disabled' => true,
	),
	array(
		'label' => __( 'Option Three', 'tutor' ),
		'value' => 'three',
		'icon'  => Icon::CERTIFICATE,
	),
	array(
		'label' => __( 'Option Four', 'tutor' ),
		'value' => 'four',
	),
	array(
		'label' => __( 'Option Five', 'tutor' ),
		'value' => 'five',
	),
	array(
		'label' => __( 'Option Six', 'tutor' ),
		'value' => 'six',
	),
	array(
		'label' => __( 'Option Seven', 'tutor' ),
		'value' => 'seven',
	),
	array(
		'label' => __( 'Option Eight', 'tutor' ),
		'value' => 'eight',
	),
);

$component_vars = array(
	'options'     => $dropdown_options,
	'placeholder' => __( 'Select an option...', 'tutor' ),
);

?>

<div class="tutor-p-6 tutor-space-y-4">
	<h3 class="tutor-text-xl tutor-font-medium">
		<?php echo esc_html__( 'Select Dropdown Demo', 'tutor' ); ?>
	</h3>

	<?php tutor_load_template( 'components.select-dropdown', $component_vars ); ?>
</div>


