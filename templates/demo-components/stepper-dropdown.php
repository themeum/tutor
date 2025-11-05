<?php
/**
 * Demo: Stepper Dropdown
 *
 * @package TutorLMS\Templates
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

?>

<div class="tutor-p-6 tutor-space-y-4">
	<h3 class="tutor-text-xl tutor-font-medium">
		<?php echo esc_html__( 'Stepper Dropdown Demo', 'tutor' ); ?>
	</h3>

	<div
		x-data='tutorStepperDropdown({
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
						class="tutor-stepper-dropdown-option"
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
							<span class="tutor-stepper-dropdown-option-icon" x-data="tutorIcon({ name: opt.icon })"></span>
						</template>
						<span class="tutor-stepper-dropdown-option-label" x-text="opt.label"></span>
					</li>
				</template>
			</ul>
		</div>
	</div>
</div>

