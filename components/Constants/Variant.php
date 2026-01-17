<?php
/**
 * Button Variant constants
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components\Constants;

defined( 'ABSPATH' ) || exit;
/**
 * Class for containing variant constants
 *
 * @since 4.0.0
 */
abstract class Variant {

	/**
	 * Semantic Variants
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public const PRIMARY      = 'primary';
	public const PRIMARY_SOFT = 'primary-soft';

	public const DESTRUCTIVE      = 'destructive';
	public const DESTRUCTIVE_SOFT = 'destructive-soft';

	public const SECONDARY = 'secondary';
	public const OUTLINE   = 'outline';
	public const GHOST     = 'ghost';
	public const LINK      = 'link';

	// Badge-only variants.
	public const INFO          = 'info';
	public const WARNING       = 'warning';
	public const SUCCESS       = 'success';
	public const SUCCESS_SOLID = 'success-solid';
	public const ERROR         = 'error';
	public const HIGHLIGHT     = 'highlight';
}
