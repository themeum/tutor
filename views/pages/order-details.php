<?php
/**
 * Course List Template.
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_script( 'tutor-order-details', tutor()->url . 'assets/js/tutor-order-details.min.js', array( 'jquery', 'wp-i18n' ), TUTOR_VERSION, true );
?>
<div id="tutor-order-details-root"></div>
<?php wp_print_footer_scripts(); ?>