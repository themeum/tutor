<?php
/**
 * Tutor Component: InputField
 *
 * Provides a fluent builder for rendering various input field types with
 * labels, validation states, and helper text.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use ReflectionClass;
use Tutor\Components\Constants\InputType;
use Tutor\Components\Constants\Size;
use Tutor\Components\Constants\Variant;
use Tutor\Components\Button;
use TUTOR\Icon;

defined( 'ABSPATH' ) || exit;

/**
 * InputField Component Class.
 *
 * Example usage:
 * ```
 * // Text input with clear button
 * InputField::make()
 *     ->type( 'text' )
 *     ->name( 'full_name' )
 *     ->label( 'Full Name' )
 *     ->placeholder( 'Enter your full name' )
 *     ->required()
 *     ->clearable()
 *     ->help_text( 'This is a helper text.' )
 *     ->attr( 'x-bind', "register('full_name', { required: 'Name is required', minLength: { value: 2, message: 'Name must be at least 2 characters' } })")
 *     ->render();
 *
 * // Text input with left icon
 * InputField::make()
 *     ->type( 'text' )
 *     ->name( 'email' )
 *     ->label( 'Email' )
 *     ->left_icon( '<svg>...</svg>' )
 *     ->render();
 *
 * // Textarea
 * InputField::make()
 *     ->type( 'textarea' )
 *     ->name( 'bio' )
 *     ->label( 'Bio' )
 *     ->render();
 *
 * // Checkbox
 * InputField::make()
 *     ->type( 'checkbox' )
 *     ->name( 'agree' )
 *     ->label( 'I agree to terms' )
 *     ->size( 'md' )
 *     ->render();
 *
 * // Radio
 * InputField::make()
 *     ->type( 'radio' )
 *     ->name( 'gender' )
 *     ->label( 'Male' )
 *     ->value( 'male' )
 *     ->render();
 *
 * // Switch
 * InputField::make()
 *     ->type( 'switch' )
 *     ->name( 'notifications' )
 *     ->label( 'Enable notifications?' )
 *     ->size( 'md' )
 *     ->render();
 *
 * // InputField with error
 * InputField::make()
 *     ->type( 'text' )
 *     ->name( 'username' )
 *     ->label( 'Username' )
 *     ->error( 'This field is required.' )
 *     ->render();
 *
 * // Select
 * InputField::make()
 *      ->type( 'select' )
 *      ->name( 'interests' )
 *      ->label( 'Interests' )
 *      ->placeholder( 'Select your interests')
 *      ->required( 'Please select an interest')
 *      ->clearable()
 *      ->options( $interests )
 *      ->multiple()
 *      ->searchable()
 *      ->size( 'md' )
 *      ->max_selections( 2 )
 *      ->help_text( 'This is a helper next.' )
 *      ->render();
 *
 * ```
 *
 * @since 4.0.0
 */
class InputField extends BaseComponent {

	/**
	 * InputField type (text|email|password|number|textarea|checkbox|radio|switch).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $type = InputType::TEXT;

	/**
	 * InputField name attribute.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * InputField ID attribute.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * InputField value.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $value = '';

	/**
	 * InputField label text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * InputField placeholder text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $placeholder = '';

	/**
	 * Search Input placeholder text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $search_placeholder = '';

	/**
	 * Help text below input.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $help_text = '';

	/**
	 * Error message text.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $error = '';

	/**
	 * Whether input is required.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $required = false;

	/**
	 * Input field attr like alpine attribute for validation
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $attr = '';

	/**
	 * Whether input is disabled.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $disabled = false;

	/**
	 * Whether input is loading
	 *
	 * @since 4.0.0
	 *
	 * @var boolean
	 */
	protected $loading = false;

	/**
	 * Whether input is checked (checkbox/radio/switch).
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $checked = false;

	/**
	 * Whether checkbox is intermediate state.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $intermediate = false;

	/**
	 * Whether input has clear button.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $clearable = false;

	/**
	 * Whether input is searchable.
	 *
	 * @since 4.0.0
	 *
	 * @var boolean
	 */
	protected $searchable = false;

	/**
	 * Whether multiple option can be selected in input.
	 *
	 * @since 4.0.0
	 *
	 * @var boolean
	 */
	protected $multiple = false;

	/**
	 * Left icon SVG markup.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $left_icon = '';

	/**
	 * Right icon SVG markup.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $right_icon = '';

	/**
	 * InputField size (sm|md).
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $size = Size::MD;

	/**
	 * Options for select input field.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $options = array();



	/**
	 * Grouped option for input field.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $groups = array();

	/**
	 * Max number of input option to be selected.
	 *
	 * @since 4.0.0
	 *
	 * @var integer
	 */
	protected $max_selections = 0;

	/**
	 * Close select input on selecting option.
	 *
	 * @since 4.0.0
	 *
	 * @var boolean
	 */
	protected $close_on_select = null;

	/**
	 * Max Height for select input.
	 *
	 * @since 4.0.0
	 *
	 * @var integer
	 */
	protected $max_height = 280;

	/**
	 * Empty state message for input.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $empty_message = '';

	/**
	 * Loading state message for input.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $loading_message = '';

	/**
	 * Selection time mode (12|24).
	 *
	 * @since 4.0.0
	 *
	 * @var int|null
	 */
	protected $selection_time_mode = null;



	/**
	 * Whether to show password strength meter.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $show_strength = false;

	/**
	 * Minimum password strength required.
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */
	protected $min_strength = 3;

	/**
	 * Set input type.
	 *
	 * @since 4.0.0
	 *
	 * @param string $type InputField type.
	 *
	 * @return $this
	 */
	public function type( $type ) {
		$allowed = $this->get_allowed_types();
		if ( in_array( $type, $allowed, true ) ) {
			$this->type = $type;
		}
		return $this;
	}

	/**
	 * Get allowed input types
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function get_allowed_types() {
		$class     = new ReflectionClass( InputType::class );
		$constants = $class->getConstants();

		return array_values( $constants );
	}

	/**
	 * Set input name.
	 *
	 * @since 4.0.0
	 *
	 * @param string $name InputField name.
	 *
	 * @return $this
	 */
	public function name( $name ) {
		$this->name = sanitize_key( $name );
		return $this;
	}

	/**
	 * Set input ID.
	 *
	 * @since 4.0.0
	 *
	 * @param string $id InputField ID.
	 *
	 * @return $this
	 */
	public function id( $id ) {
		$this->id = sanitize_key( $id );
		return $this;
	}

	/**
	 * Set input value.
	 *
	 * @since 4.0.0
	 *
	 * @param string $value InputField value.
	 *
	 * @return $this
	 */
	public function value( $value ) {
		$this->value = $value;
		return $this;
	}

	/**
	 * Set input label.
	 *
	 * @since 4.0.0
	 *
	 * @param string $label Label text.
	 *
	 * @return $this
	 */
	public function label( $label ) {
		$this->label = $label;
		return $this;
	}

	/**
	 * Set input placeholder.
	 *
	 * @since 4.0.0
	 *
	 * @param string $placeholder Placeholder text.
	 * @param bool   $search whether placeholder is for search input.
	 *
	 * @return $this
	 */
	public function placeholder( $placeholder, $search = false ) {
		if ( $search ) {
			$this->search_placeholder = $placeholder;
		} else {
			$this->placeholder = $placeholder;
		}
		return $this;
	}

	/**
	 * Set help text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $text Help text.
	 *
	 * @return $this
	 */
	public function help_text( $text ) {
		$this->help_text = $text;
		return $this;
	}

	/**
	 * Set the max height for select input
	 *
	 * @since 4.0.0
	 *
	 * @param integer $max_height the max height.
	 *
	 * @return self
	 */
	public function max_height( $max_height = 280 ): self {
		$this->max_height = $max_height;
		return $this;
	}

	/**
	 * Collapse select input on selecting option.
	 *
	 * @since 4.0.0
	 *
	 * @param boolean $close_on_select close on selecting option.
	 *
	 * @return self
	 */
	public function collapsable( $close_on_select = true ): self {
		$this->close_on_select = $close_on_select;
		return $this;
	}

	/**
	 * Set error message.
	 *
	 * @since 4.0.0
	 *
	 * @param string $error Error message.
	 *
	 * @return $this
	 */
	public function error( $error ) {
		$this->error = $error;
		return $this;
	}

	/**
	 * Set required state.
	 *
	 * @since 4.0.0
	 *
	 * @param bool|string $required Whether input is required.
	 *
	 * @return $this
	 */
	public function required( $required = true ) {
		$this->required = $required;
		return $this;
	}

	/**
	 * Set disabled state.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $disabled Whether input is disabled.
	 *
	 * @return $this
	 */
	public function disabled( $disabled = true ) {
		$this->disabled = (bool) $disabled;
		return $this;
	}

	/**
	 * Set loading state.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $loading Whether input is loading.
	 *
	 * @return $this
	 */
	public function loading( $loading = true ) {
		$this->loading = (bool) $loading;
		return $this;
	}

	/**
	 * Set checked state.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $checked Whether input is checked.
	 *
	 * @return $this
	 */
	public function checked( $checked = true ) {
		$this->checked = (bool) $checked;
		return $this;
	}

	/**
	 * Set intermediate state for checkbox.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $intermediate Whether checkbox is intermediate.
	 *
	 * @return $this
	 */
	public function intermediate( $intermediate = true ) {
		$this->intermediate = $intermediate;

		return $this;
	}

	/**
	 * Enable WordPress media library instead of native file input.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $use_wp_media Whether to use WordPress media library.
	 *
	 * @return $this
	 */
	public function use_wp_media( $use_wp_media = true ) {
		$this->use_wp_media = (bool) $use_wp_media;

		return $this;
	}

	/**
	 * Set WordPress media modal title.
	 *
	 * @since 4.0.0
	 *
	 * @param string $title Modal title.
	 *
	 * @return $this
	 */
	public function wp_media_title( $title ) {
		$this->wp_media_title = $title;

		return $this;
	}

	/**
	 * Set WordPress media modal button text.
	 *
	 * @since 4.0.0
	 *
	 * @param string $text Button text.
	 *
	 * @return $this
	 */
	public function wp_media_button_text( $text ) {
		$this->wp_media_button_text = $text;

		return $this;
	}

	/**
	 * Set WordPress media library type filter.
	 *
	 * @since 4.0.0
	 *
	 * @param string $type Library type (image, video, audio, application).
	 *
	 * @return $this
	 */
	public function wp_media_library_type( $type ) {
		$this->wp_media_library_type = $type;

		return $this;
	}

	/**
	 * Set component variant.
	 *
	 * @since 4.0.0
	 *
	 * @param string $variant Component variant.
	 *
	 * @return $this
	 */
	public function variant( $variant ) {
		$this->variant = $variant;

		return $this;
	}

	/**
	 * Enable clear button.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $clearable Whether to show clear button.
	 *
	 * @return $this
	 */
	public function clearable( $clearable = true ) {
		$this->clearable = (bool) $clearable;
		return $this;
	}

	/**
	 * Set left icon.
	 *
	 * @since 4.0.0
	 *
	 * @param string $icon SVG icon markup.
	 *
	 * @return $this
	 */
	public function left_icon( $icon ) {
		$this->left_icon = $icon;
		return $this;
	}

	/**
	 * Set right icon.
	 *
	 * @since 4.0.0
	 *
	 * @param string $icon SVG icon markup.
	 *
	 * @return $this
	 */
	public function right_icon( $icon ) {
		$this->right_icon = $icon;
		return $this;
	}

	/**
	 * Set input size.
	 *
	 * @since 4.0.0
	 *
	 * @param string $size Size (sm|md).
	 *
	 * @return $this
	 */
	public function size( $size ) {
		$allowed = array( 'sm', 'md', 'lg' );
		if ( in_array( $size, $allowed, true ) ) {
			$this->size = $size;
		}
		return $this;
	}

	/**
	 * Set whether multiple select enabled or not.
	 *
	 * @since 4.0.0
	 *
	 * @param boolean $multiple whether multiple select is enable.
	 *
	 * @return self
	 */
	public function multiple( $multiple = true ): self {
		$this->multiple = $multiple;
		return $this;
	}

	/**
	 * Set if input field is searchable.
	 *
	 * @since 4.0.0
	 *
	 * @param boolean $searchable whether input is searchable.
	 *
	 * @return self
	 */
	public function searchable( $searchable = true ): self {
		$this->searchable = $searchable;
		return $this;
	}

	/**
	 * Set empty message for input.
	 *
	 * @since 4.0.0
	 *
	 * @param string $empty_message the empty message.
	 *
	 * @return self
	 */
	public function empty_message( $empty_message = '' ): self {
		$this->empty_message = $empty_message;
		return $this;
	}

	/**
	 * Set loading message for input.
	 *
	 * @since 4.0.0
	 *
	 * @param string $loading_message the empty message.
	 *
	 * @return self
	 */
	public function loading_message( $loading_message = '' ): self {
		$this->loading_message = $loading_message;
		return $this;
	}

	/**
	 * Set selection time mode.
	 *
	 * @since 4.0.0
	 *
	 * @param int $mode Mode (12 or 24).
	 *
	 * @return self
	 */
	public function selection_time_mode( $mode = 12 ): self {
		$this->selection_time_mode = $mode;
		return $this;
	}
	/**
	 * Set whether to show password strength meter.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $show_strength Whether to show strength meter.
	 *
	 * @return self
	 */
	public function show_strength( $show_strength = true ): self {
		$this->show_strength = $show_strength;
		return $this;
	}

	/**
	 * Set minimum password strength.
	 *
	 * @since 4.0.0
	 *
	 * @param int $min_strength Minimum strength score (0-5).
	 *
	 * @return self
	 */
	public function min_strength( $min_strength = 3 ): self {
		$this->min_strength = $min_strength;
		return $this;
	}



	/**
	 * Options for select input field.
	 *
	 * @since 4.0.0
	 *
	 * @param array $options the options for input field.
	 *
	 * Example format for $options:
	 * ```
	 * $options = array(
	 *    array(
	 *      'label'       => '',
	 *      'value'       => '',
	 *      'icon'        => '',
	 *      'description' => '',
	 *    )
	 * );
	 * ```.
	 *
	 * @return self
	 */
	public function options( $options = array() ): self {
		$this->options = $options;
		return $this;
	}

	/**
	 * Set the number of max option to be selected.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $max_selections the number of max selections.
	 *
	 * @return self
	 */
	public function max_selections( $max_selections = 3 ): self {
		$this->max_selections = $max_selections;
		return $this;
	}

	/**
	 * Grouped Options for select input field.
	 *
	 * @since 4.0.0
	 *
	 * @param array $groups the options for input field.
	 *
	 * Example format for $groups:
	 *
	 * ```
	 * $groups = array(
	 *    array(
	 *       'label'   => '',
	 *       'options' => array(
	 *           array(
	 *               'label' => '',
	 *               'value' => '',
	 *           ),
	 *           array(
	 *               'label' => '',
	 *               'value' => '',
	 *           ),
	 *           array(
	 *               'label' => '',
	 *               'value' => '',
	 *           ),
	 *       ),
	 *   ),
	 * );
	 * ```.
	 *
	 * @return self
	 */
	public function groups( $groups = array() ): self {
		$this->groups = $groups;
		return $this;
	}

	/**
	 * Render select input trigger button.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function render_select_input_button(): string {

		$single_input = '
			<div class="tutor-select-value">
				<template x-if="selectedOptions.length > 0 && selectedOptions[0].icon">
					<span class="tutor-select-value-icon" x-data="tutorIcon({ name: selectedOptions[0].icon })"></span>
				</template>
				<span 
					class="tutor-select-value-text"
					:class="{ \'tutor-select-value-placeholder\': selectedValues.size === 0 }"
					x-text="displayValue"
				></span>
			</div>';

		$multiple_input = sprintf(
			'
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
							:aria-label="\'Remove \' + option.label"
						>
						%s
						</button>
					</span>
				</template>
			</div>
		',
			tutor_utils()->get_svg_icon( Icon::CROSS, 12, 12 )
		);

		$right_icon_html = $this->right_icon ? $this->right_icon : tutor_utils()->get_svg_icon( Icon::CHEVRON_DOWN, 16, 16 );

		$input_button = $this->multiple ? $multiple_input : $single_input;

		return sprintf(
			'<button
				type="button"
				class="tutor-select-trigger"
				data-select-trigger
				@click="toggle()"
				:aria-expanded="isOpen.toString()"
				:aria-haspopup="\'listbox\'"
				:disabled="disabled"
			>
			%s
				<div class="tutor-select-actions" x-cloak>
					<template x-if="canClear">
						<button
							type="button"
							class="tutor-select-clear"
							@click.stop="clear($event)"
							aria-label="%s"
						>
						%s
						</button>
					</template>
					<span class="tutor-select-arrow" :data-open="isOpen.toString()">%s</span>
				</div>
			</button>
			',
			$input_button,
			esc_attr__( 'Clear selection', 'tutor' ),
			tutor_utils()->get_svg_icon( Icon::CROSS, 16, 16 ),
			$right_icon_html
		);
	}

	/**
	 * Render option values for different selection.
	 *
	 * @since 4.0.0
	 *
	 * @param boolean $is_grouped whether the options are grouped.
	 *
	 * @return string
	 */
	protected function render_selection_option( $is_grouped = false ): string {
		$option_type = $is_grouped ? 'group.options' : 'filteredOptions';

		return sprintf(
			'
			<template x-for="(option, optionIndex) in %s" :key="option.value">
				<div
					class="tutor-select-option"
					data-select-option
					:data-disabled="option.disabled ? \'true\' : \'false\'"
					:data-selected="isSelected(option) ? \'true\' : \'false\'"
					:data-highlighted="isHighlighted(filteredOptions.indexOf(option)) ? \'true\' : \'false\'"
					@click="selectOption(option)"
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
			',
			$option_type
		);
	}

	/**
	 * Get grouped select option markup
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_grouped_option_markup(): string {
		return sprintf(
			'
		<template x-if="!isLoading && !loading && hasGroups">
			<template x-for="(group, groupIndex) in filteredGroups" :key="groupIndex">
				<div class="tutor-select-group">
					<div class="tutor-select-group-label" x-text="group.label"></div>
					<div class="tutor-select-group-options">
						%s
					</div>
				</div>
			</template>
		</template>',
			$this->render_selection_option( true )
		);
	}

	/**
	 * Get flat select option markup
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_flat_option_markup(): string {
		return sprintf(
			'
			<template x-if="!isLoading && !loading && !hasGroups">
				%s
			</template>
			',
			$this->render_selection_option()
		);
	}

	/**
	 * Get select input search field markup
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_search_input_markup(): string {

		$left_icon_html = $this->left_icon ? $this->left_icon : tutor_utils()->get_svg_icon( Icon::SEARCH_2, 20, 20 );

		$search_input = sprintf(
			'
			<template x-if="searchable">
				<div class="tutor-select-search">
					<span class="tutor-select-search-icon">
							%s
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
				</div>
			</template>
			',
			$left_icon_html
		);

		return $search_input;
	}

	/**
	 * Render select input option list.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function render_select_input_options(): string {

		$loading_state = '
			<template x-if="isLoading || loading">
				<div class="tutor-select-loading">
					<span class="tutor-select-loading-spinner"></span>
					<span x-text="loadingMessage"></span>
				</div>
			</template>';

		$empty_state = '
			<template x-if="!isLoading && !loading && filteredOptions.length === 0">
				<div class="tutor-select-empty" x-text="emptyMessage"></div>
			</template>';

		$grouped_options = $this->get_grouped_option_markup();
		$flat_options    = $this->get_flat_option_markup();

		return sprintf(
			'<div
				x-show="isOpen"
				x-cloak
				x-transition
				@click.outside="close()"
				class="tutor-select-menu"
				data-select-menu
				:data-position="dropdownPosition"
				:style="{ maxHeight: maxHeight + \'px\' }"
			>
			%s
				<div class="tutor-select-options">
					%s
					%s
					%s
					%s
				</div>
			</div>',
			$this->get_search_input_markup(),
			$loading_state,
			$empty_state,
			$grouped_options,
			$flat_options
		);
	}

	/**
	 * Render the Select Input Component HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function render_select_input(): string {

		if ( ! count( $this->options ) && ! count( $this->groups ) ) {
			return '';
		}

		$props = array(
			'options'           => $this->options,
			'groups'            => $this->groups,

			'searchable'        => $this->searchable,
			'clearable'         => $this->clearable,
			'disabled'          => $this->disabled,
			'loading'           => $this->loading,

			'multiple'          => $this->multiple,

			'name'              => $this->name,
			'required'          => $this->required,

			'placeholder'       => $this->placeholder,
			'searchPlaceholder' => $this->search_placeholder,
			'emptyMessage'      => $this->empty_message,
			'loadingMessage'    => $this->loading_message,
			'maxHeight'         => $this->max_height,

		);

		if ( $this->value ) {
			$props['value'] = $this->value;
		}

		if ( $this->max_selections ) {
			$props['maxSelections'] = $this->max_selections;
		}

		if ( null !== $this->close_on_select ) {
			$props['closeOnSelect'] = $this->close_on_select;
		}

		$size_class = '';
		if ( Size::SM === $this->size ) {
			$size_class = 'tutor-select-sm';
		} elseif ( Size::LG === $this->size ) {
			$size_class = 'tutor-select-lg';
		}

		$props_json           = htmlspecialchars( wp_json_encode( $props ), ENT_QUOTES, 'UTF-8' );
		$select_input_buttons = $this->render_select_input_button() ?? '';
		$select_input_options = $this->render_select_input_options() ?? '';

		return sprintf(
			'<div
				x-data="tutorSelect(%s)"
				class="tutor-select %s"
				:data-disabled="disabled.toString()"
				%s
			>
				%s
				%s
			</div>',
			$props_json,
			$size_class,
			$this->get_attributes_string(),
			$select_input_buttons,
			$select_input_options
		);
	}

	/**
	 * Render password input.
	 *
	 * @since 4.0.0
	 *
	 * @return string Password HTML.
	 */
	protected function render_password_input() {
		$input_id = ! empty( $this->id ) ? $this->id : $this->name;

		$input_classes = 'tutor-input';
		if ( Size::SM === $this->size ) {
			$input_classes .= ' tutor-input-sm';
		} elseif ( Size::LG === $this->size ) {
			$input_classes .= ' tutor-input-lg';
		}

		// Password inputs always have a right icon (toggle button).
		$input_classes .= ' tutor-input-content-right';

		if ( ! empty( $this->left_icon ) ) {
			$input_classes .= ' tutor-input-content-left';
		}

		$input_attrs = sprintf(
			'type="password" id="%s" name="%s" class="%s" %s',
			esc_attr( $input_id ),
			esc_attr( $this->name ),
			esc_attr( $input_classes ),
			$this->get_attributes_string()
		);

		if ( ! empty( $this->placeholder ) ) {
			$input_attrs .= sprintf( ' placeholder="%s"', esc_attr( $this->placeholder ) );
		}

		if ( ! empty( $this->value ) ) {
			$input_attrs .= sprintf( ' value="%s"', esc_attr( $this->value ) );
		}

		if ( $this->disabled ) {
			$input_attrs .= ' disabled';
		}

		$left_icon_html = '';
		if ( ! empty( $this->left_icon ) ) {
			$left_icon_html = sprintf(
				'<div class="tutor-input-content tutor-input-content-left">%s</div>',
				$this->left_icon
			);
		}

		// Toggle button is always present for password fields.
		$toggle_button = '
			<button 
				type="button" 
				class="tutor-input-password-toggle"
				x-bind="getToggleBindings()"
			>
				<span x-show="!showPassword" x-cloak>' . tutor_utils()->get_svg_icon( Icon::EYE_OFF, 16, 16 ) . '</span>
				<span x-show="showPassword" x-cloak>' . tutor_utils()->get_svg_icon( Icon::EYE, 16, 16 ) . '</span>
			</button>
		';

		$right_icon_html = sprintf(
			'<div class="tutor-input-content tutor-input-content-right">%s</div>',
			$toggle_button
		);

		return sprintf(
			'<div class="tutor-input-wrapper">
				<input %s>
				%s
				%s
			</div>',
			$input_attrs,
			$left_icon_html,
			$right_icon_html
		);
	}


	/**
	 * Render text/email/password/number input.
	 *
	 * @since 4.0.0
	 *
	 * @return string InputField HTML.
	 */
	protected function render_text_input() {
		$input_id = ! empty( $this->id ) ? $this->id : $this->name;

		$input_classes = 'tutor-input';

		if ( Size::SM === $this->size ) {
			$input_classes .= ' tutor-input-sm';
		} elseif ( Size::LG === $this->size ) {
			$input_classes .= ' tutor-input-lg';
		}

		if ( ! empty( $this->left_icon ) ) {
			$input_classes .= ' tutor-input-content-left';
		}
		if ( ! empty( $this->right_icon ) ) {
			$input_classes .= ' tutor-input-content-right';
		}
		if ( ! $this->disabled && $this->clearable ) {
			$input_classes .= ' tutor-input-content-clear';
		}

		$input_attrs = sprintf(
			'type="%s" id="%s" name="%s" class="%s" %s',
			esc_attr( $this->type ),
			esc_attr( $input_id ),
			esc_attr( $this->name ),
			esc_attr( $input_classes ),
			$this->get_attributes_string()
		);

		if ( ! empty( $this->placeholder ) ) {
			$input_attrs .= sprintf( ' placeholder="%s"', esc_attr( $this->placeholder ) );
		}

		if ( ! empty( $this->value ) ) {
			$input_attrs .= sprintf( ' value="%s"', esc_attr( $this->value ) );
		}

		if ( $this->disabled ) {
			$input_attrs .= ' disabled';
		}

		$left_icon_html = '';
		if ( ! empty( $this->left_icon ) ) {
			$left_icon_html = sprintf(
				'<div class="tutor-input-content tutor-input-content-left">%s</div>',
				$this->left_icon
			);
		}

		$right_icon_html = '';
		if ( ! empty( $this->right_icon ) ) {
			$right_icon_html = sprintf(
				'<div class="tutor-input-content tutor-input-content-right">%s</div>',
				$this->right_icon
			);
		}

		$clear_button_html = '';
		if ( ! $this->disabled && $this->clearable ) {
			$clear_icon = '';
			if ( function_exists( 'tutor_utils' ) ) {
				ob_start();
				tutor_utils()->render_svg_icon( 'cross', 16, 16 );
				$clear_icon = ob_get_clean();
			}

			$clear_button_html = sprintf(
				'<button 
					type="button"
					class="tutor-input-clear-button"
					aria-label="Clear input"
					x-cloak
					x-show="values.%1$s && String(values.%1$s).length > 0"
					@click="setValue(\'%1$s\', \'\')"
				>%2$s</button>',
				esc_attr( $this->name ),
				$clear_icon
			);
		}

		return sprintf(
			'<div class="tutor-input-wrapper">
				<input %s>
				%s
				%s
				%s
			</div>
			',
			$input_attrs,
			$left_icon_html,
			$right_icon_html,
			$clear_button_html
		);
	}

	/**
	 * Render textarea input.
	 *
	 * @since 4.0.0
	 *
	 * @return string Textarea HTML.
	 */
	protected function render_textarea() {
		$input_id = ! empty( $this->id ) ? $this->id : $this->name;

		$input_classes = 'tutor-input tutor-text-area';

		if ( Size::SM === $this->size ) {
			$input_classes .= ' tutor-input-sm';
		} elseif ( Size::LG === $this->size ) {
			$input_classes .= ' tutor-input-lg';
		}

		if ( $this->clearable ) {
			$input_classes .= ' tutor-input-content-clear';
		}

		$input_attrs = sprintf(
			'id="%s" name="%s" class="%s" %s',
			esc_attr( $input_id ),
			esc_attr( $this->name ),
			esc_attr( $input_classes ),
			$this->get_attributes_string()
		);

		if ( ! empty( $this->placeholder ) ) {
			$input_attrs .= sprintf( ' placeholder="%s"', esc_attr( $this->placeholder ) );
		}

		if ( $this->disabled ) {
			$input_attrs .= ' disabled';
		}

		$clear_button_html = '';
		if ( $this->clearable ) {
			$clear_icon = '';
			if ( function_exists( 'tutor_utils' ) ) {
				ob_start();
				tutor_utils()->render_svg_icon( 'cross', 16, 16 );
				$clear_icon = ob_get_clean();
			}
			$clear_button_html = sprintf(
				'<button 
					type="button" 
					class="tutor-input-clear-button" 
					aria-label="Clear input"
					x-cloak
					x-show="values.%1$s && String(values.%1$s).length > 0"
					@click="setValue(\'%1$s\', \'\')"
				>%2$s</button>',
				esc_attr( $this->name ),
				$clear_icon
			);
		}

		return sprintf(
			'<div class="tutor-input-wrapper">
				<textarea %s>%s</textarea>
				%s
			</div>',
			$input_attrs,
			esc_textarea( $this->value ),
			$clear_button_html,
		);
	}

	/**
	 * Render checkbox input.
	 *
	 * @since 4.0.0
	 *
	 * @return string Checkbox HTML.
	 */
	protected function render_checkbox() {
		$input_id = ! empty( $this->id ) ? $this->id : $this->name;

		$input_classes = 'tutor-checkbox';
		if ( 'md' === $this->size ) {
			$input_classes .= ' tutor-checkbox-md';
		}
		if ( $this->intermediate ) {
			$input_classes .= ' tutor-checkbox-intermediate';
		}

		$input_attrs = sprintf(
			'type="checkbox" id="%s" name="%s" class="%s" %s',
			esc_attr( $input_id ),
			esc_attr( $this->name ),
			esc_attr( $input_classes ),
			$this->get_attributes_string()
		);

		if ( ! empty( $this->value ) ) {
			$input_attrs .= sprintf( ' value="%s"', esc_attr( $this->value ) );
		}

		if ( $this->checked ) {
			$input_attrs .= ' checked';
		}

		if ( $this->disabled ) {
			$input_attrs .= ' disabled';
		}

		return sprintf(
			'<div class="tutor-input-wrapper">
				<input %s>
				<label for="%s" class="tutor-label%s">%s</label>
			</div>',
			$input_attrs,
			esc_attr( $input_id ),
			$this->required ? ' tutor-label-required' : '',
			$this->esc( $this->label )
		);
	}

	/**
	 * Render radio input.
	 *
	 * @since 4.0.0
	 *
	 * @return string Radio HTML.
	 */
	protected function render_radio() {
		$input_id = ! empty( $this->id ) ? $this->id : $this->name;

		$input_classes = 'tutor-radio';
		if ( 'md' === $this->size ) {
			$input_classes .= ' tutor-radio-md';
		}

		$input_attrs = sprintf(
			'type="radio" id="%s" name="%s" class="%s" %s',
			esc_attr( $input_id ),
			esc_attr( $this->name ),
			esc_attr( $input_classes ),
			$this->get_attributes_string()
		);

		if ( ! empty( $this->value ) ) {
			$input_attrs .= sprintf( ' value="%s"', esc_attr( $this->value ) );
		}

		if ( $this->checked ) {
			$input_attrs .= ' checked';
		}

		if ( $this->disabled ) {
			$input_attrs .= ' disabled';
		}

		return sprintf(
			'<div class="tutor-input-wrapper">
				<input %s>
				<label for="%s" class="tutor-label%s">%s</label>
			</div>',
			$input_attrs,
			esc_attr( $input_id ),
			$this->required ? ' tutor-label-required' : '',
			$this->esc( $this->label )
		);
	}

	/**
	 * Render switch input.
	 *
	 * @since 4.0.0
	 *
	 * @return string Switch HTML.
	 */
	protected function render_switch() {
		$input_id = ! empty( $this->id ) ? $this->id : $this->name;

		$input_classes = 'tutor-switch';
		if ( 'md' === $this->size ) {
			$input_classes .= ' tutor-switch-md';
		}
		if ( $this->intermediate ) {
			$input_classes .= ' tutor-switch--intermediate';
		}

		$input_attrs = sprintf(
			'type="checkbox" id="%s" name="%s" class="%s" %s',
			esc_attr( $input_id ),
			esc_attr( $this->name ),
			esc_attr( $input_classes ),
			$this->get_attributes_string()
		);

		if ( ! empty( $this->value ) ) {
			$input_attrs .= sprintf( ' value="%s"', esc_attr( $this->value ) );
		}

		if ( $this->checked ) {
			$input_attrs .= ' checked';
		}

		if ( $this->disabled ) {
			$input_attrs .= ' disabled';
		}

		return sprintf(
			'<div class="tutor-input-wrapper">
				<input %s>
				<label for="%s" class="tutor-label%s">%s</label>
			</div>',
			$input_attrs,
			esc_attr( $input_id ),
			$this->required ? ' tutor-label-required' : '',
			$this->esc( $this->label )
		);
	}

	/**
	 * Render date/time input.
	 *
	 * @since 4.0.0
	 *
	 * @return string InputField HTML.
	 */
	protected function render_date_input() {
		$original_type      = $this->type;
		$original_left_icon = $this->left_icon;
		$this->type         = 'text';

		if ( empty( $this->left_icon ) && empty( $this->right_icon ) && function_exists( 'tutor_utils' ) ) {
			$this->left_icon = tutor_utils()->get_svg_icon( Icon::CALENDAR_2, 20, 20 );
		}

		$options = array(
			'inputMode' => true,
		);

		if ( InputType::DATE_TIME === $original_type ) {
			$options['selectionTimeMode'] = $this->selection_time_mode ? $this->selection_time_mode : 12;
		} elseif ( ! is_null( $this->selection_time_mode ) ) {
			$options['selectionTimeMode'] = $this->selection_time_mode;
		}

		$config = array(
			'options' => $options,
		);

		$json_config = wp_json_encode( $config );
		$this->attr( 'x-data', "tutorCalendar($json_config)" );
		$this->attr( 'readonly', 'readonly' );

		$html = $this->render_text_input();

		$this->type      = $original_type;
		$this->left_icon = $original_left_icon;

		return $html;
	}



	/**
	 * Get the input field HTML.
	 *
	 * @since 4.0.0
	 *
	 * @return string HTML output.
	 */
	public function get(): string {
		if ( empty( $this->name ) ) {
			return '';
		}

		$input_id = ! empty( $this->id ) ? $this->id : $this->name;

		// Field wrapper classes.
		$field_classes = 'tutor-input-field';
		if ( ! empty( $this->error ) ) {
			$field_classes .= ' tutor-input-field-error';
		}

		// Render label for text inputs.
		$label_html = '';
		if ( ! in_array( $this->type, array( InputType::CHECKBOX, InputType::RADIO, InputType::SWITCH ), true ) && ! empty( $this->label ) ) {
			$label_html = sprintf(
				'<label for="%s" class="tutor-label%s">%s</label>',
				esc_attr( $input_id ),
				$this->required ? ' tutor-label-required' : '',
				$this->esc( $this->label )
			);
		}

		// Render input based on type.
		$input_html = '';
		$root_attrs = '';
		switch ( $this->type ) {
			case InputType::PASSWORD:
				$input_html = $this->render_password_input();

				$password_props = array(
					'showStrength' => $this->show_strength,
					'minStrength'  => $this->min_strength,
				);
				$alpine_data    = sprintf( 'tutorPasswordInput(%s)', htmlspecialchars( wp_json_encode( $password_props ), ENT_QUOTES, 'UTF-8' ) );
				$root_attrs     = sprintf( ' x-data="%s"', $alpine_data );
				break;
			case InputType::TEXTAREA:
				$input_html = $this->render_textarea();
				break;
			case InputType::CHECKBOX:
				$input_html = $this->render_checkbox();
				break;
			case InputType::RADIO:
				$input_html = $this->render_radio();
				break;
			case InputType::SWITCH:
				$input_html = $this->render_switch();
				break;
			case InputType::DATE:
			case InputType::DATE_TIME:
				$input_html = $this->render_date_input();
				break;
			case InputType::SELECT:
				$input_html = $this->render_select_input();
				break;

			default:
				$input_html = $this->render_text_input();
				break;
		}

		$error_html = sprintf(
			'<div 
				class="tutor-error-text" 
				x-cloak 
				x-show="errors.%1$s" 
				x-text="errors?.%1$s?.message" 
				role="alert" 
				aria-live="polite"
			></div>',
			esc_attr( $this->name )
		);

		$help_html = sprintf(
			'<div
				class="tutor-help-text"
				x-show="!errors?.%1$s?.message"
			>%2$s</div>',
			esc_attr( $this->name ),
			esc_html( $this->help_text )
		);

		$strength_meter_html = '';
		if ( InputType::PASSWORD === $this->type && $this->show_strength ) {
			$strength_meter_html = sprintf(
				'<div 
					class="tutor-help-text" 
					x-show="password.length > 0" 
					x-cloak 
					x-bind="getStrengthTextBindings()"
				></div>'
			);
		}

		$this->component_string = sprintf(
			'<div class="%s" :class="{ \'tutor-input-field-error\': errors.%s }" %s>%s%s%s%s%s</div>',
			esc_attr( $field_classes ),
			esc_attr( $this->name ),
			$root_attrs,
			$label_html,
			$input_html,
			$error_html,
			$help_html,
			$strength_meter_html
		);

		return $this->component_string;
	}
}
