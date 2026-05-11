<?php
/**
 * Position constants for the components
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * This class provides various position constants.
 *
 * @since 4.0.0
 */
abstract class Positions {

	/**
	 * Position Types
	 *
	 * @since 4.0.0
	 */
	public const LEFT         = 'left';
	public const LEFT_TOP     = 'left-top';
	public const LEFT_BOTTOM  = 'left-bottom';
	public const RIGHT        = 'right';
	public const RIGHT_TOP    = 'right-top';
	public const RIGHT_BOTTOM = 'right-bottom';
	public const TOP          = 'top';
	public const TOP_START    = 'top-start';
	public const TOP_END      = 'top-end';
	public const BOTTOM       = 'bottom';
	public const BOTTOM_START = 'bottom-start';
	public const BOTTOM_END   = 'bottom-end';
	public const CENTER       = 'center';
}
