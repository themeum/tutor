<?php
/**
 *  Size constants for the components
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Class for containing size constants
 *
 * @since 4.0.0
 */
abstract class Size {

	/**
	 *  Numeric sizes
	 *
	 * @since 4.0.0
	 *
	 * @var int
	 */

	public const SIZE_20  = 20;
	public const SIZE_24  = 24;
	public const SIZE_32  = 32;
	public const SIZE_40  = 40;
	public const SIZE_48  = 48;
	public const SIZE_56  = 56;
	public const SIZE_64  = 64;
	public const SIZE_80  = 80;
	public const SIZE_104 = 104;

	/**
	 * Semantic sizes
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public const X_SMALL = 'x-small';
	public const SMALL   = 'small';
	public const MEDIUM  = 'medium';
	public const LARGE   = 'large';

	/**
	 * Shorthand sizes
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public const SM = 'sm';
	public const MD = 'md';
	public const LG = 'lg';
}
