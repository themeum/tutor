<?php
/**
 * Tutor dashboard quiz attempt modal body.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

?>

<div class="tutor-p-7 tutor-pt-10 tutor-flex tutor-flex-column tutor-items-center">
    <?php tutor_utils()->render_svg_icon( Icon::BIN, 100, 100 ); ?>
    <h5 class="tutor-h5 tutor-font-medium tutor-mt-8">
        <?php esc_html_e( 'Do You Want to Delete This?', 'tutor' ); ?>
    </h5>
    <p class="tutor-p3 tutor-text-secondary tutor-mt-2 tutor-text-center">
        <?php esc_html_e( 'Would you like to delete Quiz Attempt permanently? We suggest you proceed with caution.', 'tutor' ); ?>
    </p>
</div>