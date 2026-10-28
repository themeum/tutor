<?php
/**
 * Settings reset button.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template variables provided by Options_V2::view_template().
 *
 * @var \Tutor\Options_V2 $this
 * @var array            $section
 */

//phpcs:ignore -- contain safe data
echo $this->view_template( 'common/reset-button-template.php', $section );
