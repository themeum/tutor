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
	protected $size = 'sm';

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
	protected $max_selections = 3;

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
	 *
	 * @return $this
	 */
	public function placeholder( $placeholder ) {
		$this->placeholder = $placeholder;
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
	 * Set attributes.
	 *
	 * @since 4.0.0
	 *
	 * @param string $key Attribute key.
	 * @param string $value Attribute value.
	 *
	 * @return $this
	 */
	public function attr( $key, $value ) {
		$this->attributes[ $key ] = esc_attr( $value );
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
		$this->intermediate = (bool) $intermediate;
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
	 * Options for select input field.
	 *
	 * @since 4.0.0
	 *
	 * @param array $options the options for input field.
	 *
	 * Example format for $options:
	 * ```
	 * $options = array(
	 *    'label'       => '',
	 *    'value'       => '',
	 *    'icon'        => '',
	 *    'description' => '',
	 * );
	 * ```
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
	 * ```
	 * @return self
	 */
	public function groups( $groups = array() ): self {
		$this->groups = $groups;
		return $this;
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

		ob_start();
		tutor_load_template(
			'core-components.form-select',
			array(
				'options'        => $this->options,
				'groups'         => $this->groups,
				'placeholder'    => $this->placeholder,
				'name'           => $this->name,
				'required'       => $this->required,
				'searchable'     => $this->searchable,
				'multiple'       => $this->multiple,
				'clearable'      => $this->clearable,
				'max_selections' => $this->max_selections,
				'size'           => $this->size,
				'disabled'       => $this->disabled,
				'loading'        => $this->loading,
			)
		);

		$output = ob_get_clean();
		return $output;
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
		if ( ! empty( $this->left_icon ) ) {
			$input_classes .= ' tutor-input-content-left';
		}
		if ( ! empty( $this->right_icon ) ) {
			$input_classes .= ' tutor-input-content-right';
		}
		if ( $this->clearable ) {
			$input_classes .= ' tutor-input-content-clear';
		}

		$input_attrs = sprintf(
			'type="%s" id="%s" name="%s" class="%s" %s',
			esc_attr( $this->type ),
			esc_attr( $input_id ),
			esc_attr( $this->name ),
			esc_attr( $input_classes ),
			$this->render_attributes()
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

		}

		return sprintf(
			'<div class="tutor-input-wrapper">
				<input %s>
				%s
				%s
				%s
			</div>
			%s
			',
			$input_attrs,
			$left_icon_html,
			$right_icon_html,
			$clear_button_html,
			$error_html
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
		if ( $this->clearable ) {
			$input_classes .= ' tutor-input-content-clear';
		}

		$input_attrs = sprintf(
			'id="%s" name="%s" class="%s" %s',
			esc_attr( $input_id ),
			esc_attr( $this->name ),
			esc_attr( $input_classes ),
			$this->render_attributes()
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
				'<button type="button" class="tutor-input-clear-button" aria-label="Clear input">%s</button>',
				$clear_icon
			);
		}

		return sprintf(
			'<div class="tutor-input-wrapper">
				<textarea %s %s>%s</textarea>
				%s
			</div>',
			$input_attrs,
			esc_textarea( $this->value ),
			$clear_button_html
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
			$this->render_attributes()
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
			$this->render_attributes()
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
			$this->render_attributes()
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
		switch ( $this->type ) {
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
			case InputType::SELECT:
				$input_html = $this->render_select_input();
				break;
			default:
				$input_html = $this->render_text_input();
				break;
		}

		// Render help text or error.
		$help_html = '';
		if ( ! empty( $this->error ) ) {
			$help_html = sprintf(
				'<div class="tutor-error-text" role="alert" aria-live="polite">%s</div>',
				$this->esc( $this->error )
			);
		} elseif ( ! empty( $this->help_text ) ) {
			$help_html = sprintf(
				'<div class="tutor-help-text">%s</div>',
				$this->esc( $this->help_text )
			);
		}

		$this->component_string = sprintf(
			'<div class="%s">%s%s%s</div>',
			esc_attr( $field_classes ),
			$label_html,
			$input_html,
			$help_html
		);

		return $this->component_string;
	}
}
