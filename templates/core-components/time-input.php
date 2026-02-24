<?php
/**
 * Time Input Component
 *
 * @package TutorLMS\Templates
 * @since 4.0.0
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

$placeholder = $placeholder ?? __( 'Select time', 'tutor' );
$value       = isset( $value ) ? (string) $value : '';
$interval    = isset( $interval ) ? (int) $interval : 30;
$disabled    = isset( $disabled ) ? (bool) $disabled : false;
$clearable   = isset( $clearable ) ? (bool) $clearable : true;
$name        = $name ?? '';
$required    = $required ?? false;
$valid_time  = $valid_time ?? false;
$wrapper_class = $wrapper_class ?? 'tutor-time-input tutor-input-field';

$props = array(
	'value'       => $value,
	'interval'    => $interval,
	'placeholder' => $placeholder,
	'disabled'    => $disabled,
	'clearable'   => $clearable,
	'name'        => $name,
	'required'    => $required,
	'validTime'   => $valid_time,
);

?>

<div
	x-data='tutorTimeInput(<?php echo wp_json_encode( $props ); ?>)'
	class="<?php echo esc_attr( $wrapper_class ); ?>"
	@click.outside="handleClickOutside()"
>
	<div class="tutor-input-wrapper" x-ref="trigger">
		<input
			type="text"
			class="tutor-input tutor-input-content-left"
			:class="{ 'tutor-input-content-clear': canClear }"
			:placeholder="placeholder"
			:disabled="disabled"
			:value="value"
			@click.stop="toggleDropdown()"
			@keydown="onInputKeydown($event)"
			@input="onInputChange($event)"
			autocomplete="off"
			aria-haspopup="listbox"
			:aria-expanded="open.toString()"
			:aria-disabled="disabled.toString()"
		/>

		<span class="tutor-input-content tutor-input-content-left" aria-hidden="true">
			<?php tutor_utils()->render_svg_icon( Icon::CLOCK, 20, 20 ); ?>
		</span>

		<button
			type="button"
			class="tutor-input-clear-button"
			x-show="canClear"
			@click.stop="clearValue()"
			aria-label="<?php echo esc_attr__( 'Clear time', 'tutor' ); ?>"
		>
			<?php tutor_utils()->render_svg_icon( Icon::CROSS, 12, 12 ); ?>
		</button>
	</div>

	<div
		x-ref="content"
		class="tutor-time-input-menu"
		x-show="open"
		x-cloak
		x-transition.opacity.scale.origin.top
		@keydown="onListKeydown($event)"
	>
		<template x-for="(option, index) in options" :key="option">
			<button
				type="button"
				class="tutor-time-input-option"
				:data-option-index="index"
				:data-active="(highlightedIndex === index).toString()"
				:data-selected="(value === option).toString()"
				@click="selectOption(option)"
				@mousemove="highlightedIndex = index"
				@focus="highlightedIndex = index"
				x-text="option"
			></button>
		</template>
	</div>
</div>
