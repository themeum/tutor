<?php
/**
 * Select Dropdown Component
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
	x-data='tutorSelectDropdown({
		options: <?php echo wp_json_encode( $options_json ); ?>,
		placeholder: <?php echo wp_json_encode( $placeholder ); ?>,
		value: <?php echo wp_json_encode( $value ); ?>,
		disabled: <?php echo $disabled ? 'true' : 'false'; ?>
	})'
	class="tutor-select-dropdown"
>
	<button
		type="button"
		class="tutor-select-dropdown-control"
		:class="{ 'tutor-is-open': isOpen, 'tutor-is-disabled': disabled }"
		@click="toggle()"
		@keydown="handleKeydown($event)"
		aria-haspopup="listbox"
		:aria-expanded="isOpen.toString()"
		:aria-disabled="disabled.toString()"
	>
		<span class="tutor-select-dropdown-value" :class="{ 'tutor-select-dropdown-placeholder': !value }">
			<template x-if="selectedOption && selectedOption.icon">
				<span class="tutor-select-dropdown-value-icon" x-data="tutorIcon({ name: selectedOption.icon })"></span>
			</template>
			<span x-text="selectedLabel"></span>
		</span>
		<span class="tutor-select-dropdown-arrow" aria-hidden="true"><?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 16, 16 ); ?></span>
	</button>

	<div 
		class="tutor-select-dropdown-menu" 
		x-show="isOpen" 
		@click.outside="close()" 
		x-transition
	>
		<ul role="listbox">
			<template x-for="(opt, idx) in options" :key="opt.value">
				<li
					role="option"
					class="tutor-select-dropdown-option"
					:class="{
						'tutor-is-selected': isSelected(opt),
						'tutor-is-disabled': opt.disabled,
						'tutor-is-highlighted': highlightedIndex === idx
					}"
					:aria-selected="isSelected(opt).toString()"
					@click.prevent="selectOption(opt)"
					@mousemove="highlightedIndex = idx"
				>
					<template x-if="opt.icon">
						<span class="tutor-select-dropdown-option-icon" x-data="tutorIcon({ name: opt.icon })"></span>
					</template>
					<span class="tutor-select-dropdown-option-label" x-text="opt.label"></span>
				</li>
			</template>
		</ul>
	</div>
</div>

