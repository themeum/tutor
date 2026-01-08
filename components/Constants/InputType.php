<?php
/**
 *  Input type constants for the components
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Class for containing input type constants
 *
 * @since 4.0.0
 */
abstract class InputType {

	/**
	 * Input types
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public const TEXT     = 'text';
	public const SELECT   = 'select';
	public const RADIO    = 'radio';
	public const SWITCH   = 'switch';
	public const CHECKBOX = 'checkbox';
	public const TEXTAREA = 'textarea';
	public const EMAIL    = 'email';
	public const PASSWORD = 'password';
	public const NUMBER   = 'number';
	public const COLOR    = 'color';
	public const SEARCH   = 'search';
	public const DROPDOWN = 'dropdown';
	public const LINK     = 'link';
	public const DATE     = 'date';
	public const DATE_TIME = 'date-time';
}
