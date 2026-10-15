<?php
/**
 * Color Constants
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Class for containing color constants
 *
 * @since 4.0.0
 */
abstract class Color {

	/**
	 * Icon Colors
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public const IDLE            = 'idle';
	public const IDLE_INVERSE    = 'idle-inverse';
	public const HOVER           = 'hover';
	public const SECONDARY       = 'secondary';
	public const SUBDUED         = 'subdued';
	public const BRAND           = 'brand';
	public const BRAND_HOVER     = 'brand-hover';
	public const BRAND_SECONDARY = 'brand-secondary';
	public const EXCEPTION1      = 'exception1';
	public const EXCEPTION2      = 'exception2';
	public const SUCCESS_PRIMARY = 'success-primary';
	public const EXCEPTION4      = 'exception4';
	public const EXCEPTION5      = 'exception5';
	public const CAUTION         = 'caution';
	public const CRITICAL        = 'critical';
	public const CRITICAL_HOVER  = 'critical-hover';
	public const WARNING         = 'warning';
	public const DISABLED        = 'disabled';
}
