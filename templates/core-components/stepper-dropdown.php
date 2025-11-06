<?php
/**
 * Stepper Dropdown Component
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Default values.
$options     = isset( $options ) ? $options : array();
$placeholder = isset( $placeholder ) ? $placeholder : __( 'Select an option...', 'tutor' );
$value       = isset( $value ) ? $value : '';
$disabled    = isset( $disabled ) ? $disabled : false;
$name        = isset( $name ) ? $name : '';

$options_json = array();
foreach ( $options as $option ) {
	$option_data = array(
		'label' => isset( $option['label'] ) ? $option['label'] : '',
		'value' => isset( $option['value'] ) ? $option['value'] : '',
	);
	if ( isset( $option['icon'] ) ) {
		$option_data['icon'] = $option['icon'];
	}
	if ( isset( $option['disabled'] ) && $option['disabled'] ) {
		$option_data['disabled'] = true;
	}
	$options_json[] = $option_data;
}

?>

<div
	x-data='tutorStepperDropdown({
		options: <?php echo wp_json_encode( $options_json ); ?>,
		placeholder: <?php echo wp_json_encode( $placeholder ); ?>,
		value: <?php echo wp_json_encode( $value ); ?>,
		disabled: <?php echo $disabled ? 'true' : 'false'; ?>,
		name: <?php echo wp_json_encode( $name ); ?>
	})'
	class="tutor-stepper-dropdown"
>
	<div class="tutor-stepper-dropdown-control" :class="{ 'tutor-is-open': isOpen, 'tutor-is-disabled': disabled }">
		<button
			type="button"
			class="tutor-stepper-dropdown-value-wrapper"
			@click="toggle()"
			@keydown="handleKeydown($event)"
			aria-haspopup="listbox"
			:aria-expanded="isOpen.toString()"
			:aria-disabled="disabled.toString()"
		>
			<span class="tutor-stepper-dropdown-value" :class="{ 'tutor-stepper-dropdown-placeholder': !value }">
				<template x-if="selectedOption && selectedOption.icon">
					<span class="tutor-stepper-dropdown-value-icon" x-data="tutorIcon({ name: selectedOption.icon })"></span>
				</template>
				<span x-text="selectedLabel"></span>
			</span>
		</button>

		<div class="tutor-stepper-dropdown-stepper-container">
			<button
				type="button"
				class="tutor-stepper-dropdown-stepper-btn tutor-stepper-dropdown-stepper-btn-up"
				:class="{ 'tutor-is-disabled': !canDecrement() || disabled }"
				@click.stop="decrement()"
				:disabled="!canDecrement() || disabled"
				aria-label="<?php echo esc_attr__( 'Decrease value', 'tutor' ); ?>"
			>
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_UP, 16, 16 ); ?>
			</button>

			<button
				type="button"
				class="tutor-stepper-dropdown-stepper-btn tutor-stepper-dropdown-stepper-btn-down"
				:class="{ 'tutor-is-disabled': !canIncrement() || disabled }"
				@click.stop="increment()"
				:disabled="!canIncrement() || disabled"
				aria-label="<?php echo esc_attr__( 'Increase value', 'tutor' ); ?>"
			>
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 16, 16 ); ?>
			</button>
		</div>
	</div>

	<div 
		class="tutor-stepper-dropdown-menu" 
		x-show="isOpen" 
		@click.outside="close()" 
		x-transition
	>
		<ul role="listbox">
			<template x-for="(opt, idx) in options" :key="opt.value">
				<li
					role="option"
					class="tutor-stepper-dropdown-option tutor-d-flex tutor-align-center tutor-gap-2"
					:class="{
						'tutor-is-selected': isSelected(opt),
						'tutor-is-disabled': opt.disabled,
						'tutor-is-highlighted': highlightedIndex === idx
					}"
					:aria-selected="isSelected(opt).toString()"
					@click.prevent="selectOption(opt)"
					@mousemove="highlightedIndex = idx"
				>
					<span class="tutor-stepper-dropdown-option-check">
						<span class="tutor-stepper-dropdown-option-check-icon" x-show="isSelected(opt)">
							<?php tutor_utils()->render_svg_icon( Icon::TICK_MARK, 16, 16 ); ?>
						</span>
					</span>
					<template x-if="opt.icon">
						<span class="tutor-stepper-dropdown-option-icon" x-data="tutorIcon({ name: opt.icon })"></span>
					</template>
					<span class="tutor-stepper-dropdown-option-label" x-text="opt.label"></span>
				</li>
			</template>
		</ul>
	</div>
</div>

