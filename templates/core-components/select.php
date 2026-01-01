<?php
/**
 * Tutor Select Component
 *
 * @package TutorLMS\Templates
 * @since 4.0.0
 */

use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

// Props with defaults - organized by category.

// Data.
$options       = $options ?? array();
$groups        = $groups ?? array();
$value         = $value ?? null;
$default_value = $default_value ?? null;

// Multi-select.
$multiple       = $multiple ?? false;
$max_selections = $max_selections ?? null;

// Behavior.
$searchable      = $searchable ?? false;
$clearable       = $clearable ?? false;
$disabled        = $disabled ?? false;
$loading         = $loading ?? false;
$close_on_select = $close_on_select ?? ! $multiple;

// Display.
$placeholder        = $placeholder ?? __( 'Select...', 'tutor' );
$search_placeholder = $search_placeholder ?? __( 'Search...', 'tutor' );
$empty_message      = $empty_message ?? __( 'No options found', 'tutor' );
$loading_message    = $loading_message ?? __( 'Loading...', 'tutor' );
$max_height         = $max_height ?? 280;
$size               = $size ?? 'default'; // sm, default, lg.

// Form integration.
$name     = $name ?? '';
$required = $required ?? false;

// Prepare options JSON.
$options_json = array();
foreach ( $options as $option ) {
	$opt = array(
		'label' => $option['label'] ?? '',
		'value' => $option['value'] ?? '',
	);
	if ( isset( $option['disabled'] ) ) {
		$opt['disabled'] = (bool) $option['disabled'];
	}
	if ( isset( $option['icon'] ) ) {
		$opt['icon'] = $option['icon'];
	}
	if ( isset( $option['description'] ) ) {
		$opt['description'] = $option['description'];
	}
	if ( isset( $option['group'] ) ) {
		$opt['group'] = $option['group'];
	}
	if ( isset( $option['href'] ) ) {
		$opt['href'] = $option['href'];
	}
	$options_json[] = $opt;
}

// Prepare groups JSON.
$groups_json = array();
foreach ( $groups as $group ) {
	$grp = array(
		'label'   => $group['label'] ?? '',
		'options' => array(),
	);
	foreach ( $group['options'] ?? array() as $option ) {
		$opt = array(
			'label' => $option['label'] ?? '',
			'value' => $option['value'] ?? '',
		);
		if ( isset( $option['disabled'] ) ) {
			$opt['disabled'] = (bool) $option['disabled'];
		}
		if ( isset( $option['icon'] ) ) {
			$opt['icon'] = $option['icon'];
		}
		if ( isset( $option['description'] ) ) {
			$opt['description'] = $option['description'];
		}
		if ( isset( $option['href'] ) ) {
			$opt['href'] = $option['href'];
		}
		$grp['options'][] = $opt;
	}
	$groups_json[] = $grp;
}

// Build component props - organized by category.
$component_props = array(
	// Data.
	'options'           => $options_json,
	'groups'            => $groups_json,

	// Behavior.
	'searchable'        => $searchable,
	'clearable'         => $clearable,
	'disabled'          => $disabled,
	'loading'           => $loading,
	'closeOnSelect'     => $close_on_select,

	// Multi-select.
	'multiple'          => $multiple,

	// Display.
	'placeholder'       => $placeholder,
	'searchPlaceholder' => $search_placeholder,
	'emptyMessage'      => $empty_message,
	'loadingMessage'    => $loading_message,
	'maxHeight'         => $max_height,

	// Form integration.
	'name'              => $name,
	'required'          => $required,
);

// Add optional props.
if ( null !== $value ) {
	$component_props['value'] = $value;
} elseif ( null !== $default_value ) {
	$component_props['defaultValue'] = $default_value;
}

if ( null !== $max_selections ) {
	$component_props['maxSelections'] = $max_selections;
}

$size_class = '';
if ( 'sm' === $size ) {
	$size_class = 'tutor-select-sm';
} elseif ( 'lg' === $size ) {
	$size_class = 'tutor-select-lg';
}

?>

<?php
// Encode props for Alpine.js x-data attribute.
$props_json = htmlspecialchars( wp_json_encode( $component_props ), ENT_QUOTES, 'UTF-8' );
?>

<div
	x-data="tutorSelect(<?php echo esc_attr( $props_json ); ?>)"
	class="tutor-select <?php echo esc_attr( $size_class ); ?>"
	:data-disabled="disabled.toString()"
>
	<!-- Trigger Button -->
	<button
		type="button"
		class="tutor-select-trigger"
		data-select-trigger
		@click="toggle()"
		:aria-expanded="isOpen.toString()"
		:aria-haspopup="'listbox'"
		:disabled="disabled"
	>
		<!-- Single value display -->
		<template x-if="!multiple">
			<div class="tutor-select-value">
				<template x-if="selectedOptions.length > 0 && selectedOptions[0].icon">
					<span class="tutor-select-value-icon" x-data="tutorIcon({ name: selectedOptions[0].icon })"></span>
				</template>
				<span 
					class="tutor-select-value-text"
					:class="{ 'tutor-select-value-placeholder': selectedValues.size === 0 }"
					x-text="displayValue"
				></span>
			</div>
		</template>

		<!-- Multiple values display -->
		<template x-if="multiple">
			<div class="tutor-select-tags">
				<template x-if="selectedOptions.length === 0">
					<span class="tutor-select-value-placeholder" x-text="placeholder"></span>
				</template>
				<template x-for="option in selectedOptions" :key="option.value">
					<span class="tutor-select-tag">
						<span class="tutor-select-tag-label" x-text="option.label"></span>
						<button
							type="button"
							class="tutor-select-tag-remove"
							@click.stop="deselectOption(option, $event)"
							:aria-label="'Remove ' + option.label"
						>
							<?php tutor_utils()->render_svg_icon( Icon::CROSS, 12, 12 ); ?>
						</button>
					</span>
				</template>
			</div>
		</template>

		<!-- Actions -->
		<div class="tutor-select-actions" x-cloak>
			<template x-if="canClear">
				<button
					type="button"
					class="tutor-select-clear"
					@click.stop="clear($event)"
					aria-label="<?php esc_attr_e( 'Clear selection', 'tutor' ); ?>"
				>
					<?php tutor_utils()->render_svg_icon( Icon::CROSS, 16, 16 ); ?>
				</button>
			</template>
			<span class="tutor-select-arrow" :data-open="isOpen.toString()">
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN, 16, 16 ); ?>
			</span>
		</div>
	</button>

	<!-- Dropdown Menu -->
	<div
		x-show="isOpen"
		x-cloak
		x-transition
		@click.outside="close()"
		class="tutor-select-menu"
		data-select-menu
		:data-position="dropdownPosition"
		:style="{ maxHeight: maxHeight + 'px' }"
	>
		<!-- Search Input -->
		<template x-if="searchable">
			<div class="tutor-select-search">
				<span class="tutor-select-search-icon">
					<?php tutor_utils()->render_svg_icon( Icon::SEARCH_2, 20, 20 ); ?>
				</span>
				<input
					type="text"
					class="tutor-select-search-input"
					data-select-search
					:placeholder="searchPlaceholder"
					x-model="searchQuery"
					@input="handleSearch($event.target.value)"
					@keydown.stop
				/>
			</div>
		</template>

		<!-- Options List -->
		<div class="tutor-select-options">
			<!-- Loading State -->
			<template x-if="isLoading || loading">
				<div class="tutor-select-loading">
					<span class="tutor-select-loading-spinner"></span>
					<span x-text="loadingMessage"></span>
				</div>
			</template>

			<!-- Empty State -->
			<template x-if="!isLoading && !loading && filteredOptions.length === 0">
				<div class="tutor-select-empty" x-text="emptyMessage"></div>
			</template>

			<!-- Grouped Options -->
			<template x-if="!isLoading && !loading && hasGroups">
				<template x-for="(group, groupIndex) in filteredGroups" :key="groupIndex">
					<div class="tutor-select-group">
						<div class="tutor-select-group-label" x-text="group.label"></div>
						<div class="tutor-select-group-options">
							<template x-for="(option, optionIndex) in group.options" :key="option.value">
								<div
									class="tutor-select-option"
									data-select-option
									:data-disabled="option.disabled ? 'true' : 'false'"
									:data-selected="isSelected(option) ? 'true' : 'false'"
									:data-highlighted="isHighlighted(filteredOptions.indexOf(option)) ? 'true' : 'false'"
									@click="selectOption(option, $event)"
									@mouseenter="highlightedIndex = filteredOptions.indexOf(option)"
									role="option"
									:aria-selected="isSelected(option).toString()"
								>
									<template x-if="option.icon">
										<span class="tutor-select-option-icon" x-data="tutorIcon({ name: option.icon })"></span>
									</template>
									<div class="tutor-select-option-content">
										<div class="tutor-select-option-label" x-text="option.label"></div>
										<template x-if="option.description">
											<div class="tutor-select-option-description" x-text="option.description"></div>
										</template>
									</div>
								</div>
							</template>
						</div>
					</div>
				</template>
			</template>

			<!-- Flat Options -->
			<template x-if="!isLoading && !loading && !hasGroups">
				<template x-for="(option, index) in filteredOptions" :key="option.value">
					<div
						class="tutor-select-option"
						data-select-option
						:data-disabled="option.disabled ? 'true' : 'false'"
						:data-selected="isSelected(option) ? 'true' : 'false'"
						:data-highlighted="isHighlighted(index) ? 'true' : 'false'"
						@click="selectOption(option, $event)"
						@mouseenter="highlightedIndex = index"
						role="option"
						:aria-selected="isSelected(option).toString()"
					>
						<template x-if="option.icon">
							<span class="tutor-select-option-icon" x-data="tutorIcon({ name: option.icon })"></span>
						</template>
						<div class="tutor-select-option-content">
							<div class="tutor-select-option-label" x-text="option.label"></div>
							<template x-if="option.description">
								<div class="tutor-select-option-description" x-text="option.description"></div>
							</template>
						</div>
					</div>
				</template>
			</template>
		</div>
	</div>
</div>
