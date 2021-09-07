<?php
/**
 * Options generator
 *
 * @param object $this
 */

$url_page = isset( $_GET['tab_page'] ) ? $_GET['tab_page'] : null;

if ( isset( $_GET['tab_page'] ) && 'email' === $_GET['tab_page'] && isset( $_GET['edit'] ) ) {
	include tutor()->path . 'views/options/email-edit.php';
} else {
	include tutor()->path . 'views/options/settings-form.php';
} ?>
<style>
	.isHighlighted {}

	.tutor-notification {
		position: fixed;
		bottom: 40px;
		z-index: 999;
		opacity: 0;
		visibility: hidden;
	}

	.tutor-notification.show {
		opacity: 1;
		visibility: visible;
	}

	.tutor-notification .tutor-notification-close{
		transition: unset;
	}
</style>
