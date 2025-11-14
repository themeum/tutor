<?php
/**
 * Form-Integrated Select Field
 *
 * A wrapper around the select component that automatically
 * integrates with form validation and handles all the boilerplate.
 *
 * @package TutorLMS\Templates
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

// Field configuration.
$name      = $name ?? '';
$label     = $label ?? '';
$help_text = $help_text ?? '';

// Select component props - organized by category.
$select_props = array_merge(
	array(
		// Data.
		'options'            => $options ?? array(),
		'groups'             => $groups ?? array(),
		'value'              => $value ?? null,
		'default_value'      => $default_value ?? null,

		// Multi-select.
		'multiple'           => $multiple ?? false,
		'max_selections'     => $max_selections ?? null,

		// Behavior.
		'searchable'         => $searchable ?? false,
		'clearable'          => $clearable ?? false,
		'disabled'           => $disabled ?? false,
		'loading'            => $loading ?? false,
		'close_on_select'    => $close_on_select ?? null,

		// Display.
		'placeholder'        => $placeholder ?? __( 'Select...', 'tutor' ),
		'search_placeholder' => $search_placeholder ?? __( 'Search...', 'tutor' ),
		'empty_message'      => $empty_message ?? __( 'No options found', 'tutor' ),
		'loading_message'    => $loading_message ?? __( 'Loading...', 'tutor' ),
		'max_height'         => $max_height ?? 280,
		'size'               => $size ?? 'default',

		// Form integration.
		'name'               => $name,
		'required'           => $required ?? false,
	),
	$select_props ?? array()
);

$field_name = esc_attr( $name );

?>

<div 
	class="tutor-input-field" 
	:class="{ 'tutor-input-field-error': errors.<?php echo esc_attr( $field_name ); ?> }"
>
	<?php if ( $label ) : ?>
	<label for="<?php echo esc_attr( $field_name ); ?>" class="tutor-label">
		<?php echo esc_html( $label ); ?>
	</label>
	<?php endif; ?>

	<?php tutor_load_template( 'core-components.select', $select_props ); ?>

	<div class="tutor-error-text" x-cloak x-show="errors.<?php echo esc_attr( $field_name ); ?>" x-text="errors?.<?php echo esc_attr( $field_name ); ?>?.message" role="alert" aria-live="polite"></div>

	<?php if ( $help_text ) : ?>
	<div class="tutor-help-text" x-show="!errors?.<?php echo esc_attr( $field_name ); ?>">
		<?php echo esc_html( $help_text ); ?>
	</div>
	<?php endif; ?>
</div>
