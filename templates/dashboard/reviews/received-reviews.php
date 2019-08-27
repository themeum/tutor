<?php
/**
 * Reviews received
 *
 * @since v.1.2.13
 *
 * @author Themeum
 * @url https://themeum.com
 * @package Tutor
 */

?>

<div class="tutor-dashboard-content-inner">
    <h3><?php echo sprintf(__("My Reviews"), 'tutor');?></h3>
    <div class="tutor-dashboard-inline-links">
        <ul>
            <li><a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews'); ?>"> <?php _e('Given (15)'); ?></a> </li>
            <li class="active"><a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews/received-reviews'); ?>"> <?php _e('Received (27)'); ?></a> </li>
        </ul>
    </div>
    <div class=" tutor-course-reviews-wrap">

        <?php
            // TODO: Need get_reviews_by_instructor() function to get instructor reviews

        ?>

        <div class="tutor-reviews-list">
            Review Received Contents
        </div>
    </div>
</div>
