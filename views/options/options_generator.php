<?php

/**
 * Options generator
 * @param object $this
 */

$url_page = isset($_GET['tab_page']) ? $_GET['tab_page'] : null;

if (isset($_GET['tab_page']) && $_GET['tab_page'] == 'email' && isset($_GET['edit'])) {
    include tutor()->path . 'views/options/email-edit.php';
} else {
    include tutor()->path . 'views/options/settings-form.php';
} ?>
<style>
    .tutor-notification {
        position: fixed;
        bottom: 40px;
        z-index: 999;
        visibility: hidden;
        transition: all .3s;
    }

    .tutor-notification.show {
        visibility: visible;
    }
</style>