<?php
/**
 * My Own reviews
 *
 * @since v.1.1.2
 *
 * @author Themeum
 * @url https://themeum.com
 * @package Tutor
 */

$reviews = tutor_utils()->get_reviews_by_user();

?>
<h3><?php echo sprintf(__("My Reviews", 'tutor')); ?></h3>

<div class="tutor-dashboard-content-inner">
    <!--<div class="tutor-dashboard-inline-links">
        <ul>
            <li class="active"><a href="<?php /*echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews'); */?>"> <?php /* echo sprintf(__('Given (%s)', 'tutor'), count($reviews));  ; */?></a> </li>
            <li><a href="<?php /*echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews/received-reviews'); */?>"> <?php /*_e('Received (27)'); */?></a> </li>
        </ul>
        @TODO: CHeck received review
    </div>-->
    <div class="tutor-dashboard-reviews-wrap">

        <?php

            if ( ! is_array($reviews) || ! count($reviews)){ ?>
                <div class="tutor-dashboard-content-inner">
                    <p><?php _e("Sorry, but you are looking for something that isn't here." , 'tutor'); ?></p>
                </div>
                <?php
            }
        ?>

        <div class="tutor-dashboard-reviews">
            <?php
            foreach ($reviews as $review){
                $profile_url = tutor_utils()->profile_url($review->user_id);
                ?>
                <div class="tutor-dashboard-single-review tutor-review-<?php echo $review->comment_ID; ?>">
                    <div class="tutor-dashboard-review-header">

                        <div class="tutor-dashboard-review-heading">
                            <div class="tutor-dashboard-review-title">
                                <?php _e('Course: ', 'tutor'); ?>
                                <a href="<?php echo get_the_permalink($review->comment_post_ID); ?>"><?php echo get_the_title($review->comment_post_ID); ?></a>
                            </div>
                            <!--<p class="review-meta"><?php /* echo sprintf(__('%s ago', 'tutor'), human_time_diff(strtotime($review->comment_date)));  */?></p>-->
<!--                            <div class="tutor-dashboard-review-links">-->
<!--                                <a href="#course-rating-form"><i class="tutor-icon-pencil"></i> <span>--><?php //esc_html_e('Edit Feedback', 'tutor'); ?><!--</span></a>-->
<!--                                <a href="#"><i class="tutor-icon-garbage"></i> --><?php //esc_html_e('Delete', 'tutor'); ?><!--</a>-->
<!--                            </div>-->
                        </div>
                    </div>
                    <div class="individual-dashboard-review-body">
                        <?php tutor_utils()->star_rating_generator($review->rating); ?>
                        <?php echo wpautop(stripslashes($review->comment_content)); ?>
                    </div>

                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>


