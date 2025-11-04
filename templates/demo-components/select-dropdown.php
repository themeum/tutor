<?php
/**
 * Demo: Select Dropdown
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

?>

<div class="tutor-p-6 tutor-space-y-4">
	<h3 class="tutor-text-xl tutor-font-medium">
		<?php echo esc_html__( 'Select Dropdown Demo', 'tutor' ); ?>
	</h3>

	<div
		x-data='tutorSelectDropdown({
			options: [
				{ label: "<?php echo esc_js( __( 'Option One', 'tutor' ) ); ?>", value: "one", icon: "<?php echo esc_js( Icon::BOOK ); ?>" },
				{ label: "<?php echo esc_js( __( 'Option Two', 'tutor' ) ); ?>", value: "two", icon: "<?php echo esc_js( Icon::CALENDAR ); ?>" },
				{ label: "<?php echo esc_js( __( 'Disabled Option', 'tutor' ) ); ?>", value: "disabled", disabled: true },
				{ label: "<?php echo esc_js( __( 'Option Three', 'tutor' ) ); ?>", value: "three", icon: "<?php echo esc_js( Icon::CERTIFICATE ); ?>" },
				{ label: "<?php echo esc_js( __( 'Option Four', 'tutor' ) ); ?>", value: "four" },
				{ label: "<?php echo esc_js( __( 'Option Five', 'tutor' ) ); ?>", value: "five" },
				{ label: "<?php echo esc_js( __( 'Option Six', 'tutor' ) ); ?>", value: "six" },
				{ label: "<?php echo esc_js( __( 'Option Seven', 'tutor' ) ); ?>", value: "seven" },
				{ label: "<?php echo esc_js( __( 'Option Eight', 'tutor' ) ); ?>", value: "eight" }
			],
			placeholder: "<?php echo esc_js( __( 'Select an option...', 'tutor' ) ); ?>"
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
</div>


