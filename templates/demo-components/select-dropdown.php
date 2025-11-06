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

$component_vars_form = array(
	'options'     => $dropdown_options,
	'placeholder' => __( 'Select an option...', 'tutor' ),
	'name'        => 'selected_option',
);

$component_vars_searchable = array(
	'options'            => $dropdown_options,
	'placeholder'        => __( 'Select an option...', 'tutor' ),
	'searchable'         => true,
	'search_placeholder' => __( 'Search...', 'tutor' ),
);

$component_vars_basic = array(
	'options'     => $dropdown_options,
	'placeholder' => __( 'Select an option...', 'tutor' ),
);

?>

<div class="tutor-p-6 tutor-space-y-6">
	<h3 class="tutor-text-xl tutor-font-medium">
		<?php echo esc_html__( 'Select Dropdown Demo', 'tutor' ); ?>
	</h3>

	<div class="tutor-space-y-3">
		<h4 class="tutor-text-base tutor-font-medium">
			<?php echo esc_html__( 'Form Demo', 'tutor' ); ?>
		</h4>
		<div
			x-data="{ submittedValue: '' }"
			class="tutor-space-y-4"
		>
			<form
				@submit.prevent="submittedValue = $event.target.querySelector('input[name=\'selected_option\']')?.value || ''"
				class="tutor-space-y-4"
			>
				<?php tutor_load_template( 'components.select-dropdown', $component_vars_form ); ?>
				<button
					type="submit"
					class="tutor-btn tutor-btn-primary tutor-mt-3"
				>
					<?php echo esc_html__( 'Submit', 'tutor' ); ?>
				</button>
			</form>
			<div
				x-show="submittedValue"
				x-transition
				class="tutor-p-4 tutor-bg-gray-100 tutor-rounded tutor-border tutor-border-gray-300"
			>
				<p class="tutor-text-sm tutor-text-gray-600 tutor-mb-1">
					<?php echo esc_html__( 'Submitted Value:', 'tutor' ); ?>
				</p>
				<p class="tutor-text-base tutor-font-medium tutor-text-gray-900" x-text="submittedValue"></p>
			</div>
		</div>
	</div>

	<div class="tutor-space-y-3">
		<h4 class="tutor-text-base tutor-font-medium">
			<?php echo esc_html__( 'With Search', 'tutor' ); ?>
		</h4>
		<?php tutor_load_template( 'components.select-dropdown', $component_vars_searchable ); ?>
	</div>

	<div class="tutor-space-y-3">
		<h4 class="tutor-text-base tutor-font-medium">
			<?php echo esc_html__( 'Without Search', 'tutor' ); ?>
		</h4>
		<?php tutor_load_template( 'components.select-dropdown', $component_vars_basic ); ?>
	</div>
</div>

