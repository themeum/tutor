<?php
/**
 * Template for React-based settings page
 *
 * @package Tutor\Views
 * @subpackage Tutor\Options
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Enqueue the React settings app
wp_enqueue_script( 'tutor-settings-app' );
wp_enqueue_style( 'tutor-admin' );

// Localize script with necessary data
wp_localize_script( 'tutor-settings-app', 'tutorConfig', array(
	'ajaxurl'      => admin_url( 'admin-ajax.php' ),
	'nonce'        => wp_create_nonce( tutor()->nonce_action ),
	'nonce_key'    => tutor()->nonce,
	'nonce_action' => tutor()->nonce_action,
	'baseUrl'      => get_site_url(),
) );
?>

<div id="tutor-settings-app"></div>

<style>
/* Ensure the React app container takes full height */
#tutor-settings-app {
	min-height: 100vh;
}

/* Hide the default WordPress admin notices in the React app */
#tutor-settings-app .notice,
#tutor-settings-app .error,
#tutor-settings-app .updated {
	display: none !important;
}
</style>