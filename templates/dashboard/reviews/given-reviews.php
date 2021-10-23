<?php
/**
 * My Own reviews
 *
 * @since v.1.1.2
 *
 * @author Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$reviews = tutor_utils()->get_reviews_by_user(0, 0, 150, true);
$review_count = $reviews->count;
$reviews = $reviews->results;
$received_count = tutor_utils()->get_reviews_by_instructor(0, 0, 0)->count;
?>

<div class="tutor-dashboard-content-inner">

	<?php
	if (current_user_can(tutor()->instructor_role)){
		?>
        <div class="tutor-dashboard-inline-links">
            <ul>
                <li><a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews'); ?>"> <?php _e('Received', 'tutor'); ?> (<?php echo $received_count; ?>)</a> </li>
                <li class="active"> <a href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('reviews/given-reviews'); ?>"> <?php _e('Given', 'tutor'); ?> (<?php echo $review_count; ?>)</a> </li>
            </ul>
        </div>
	<?php } ?>

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
                $update_id = 'tutor_review_update_' . $review->comment_ID;
                $delete_id = 'tutor_review_delete_' . $review->comment_ID;
				?>
                <div class="tutor-dashboard-single-review tutor-review-<?php echo $review->comment_ID; ?>">
                    <div class="tutor-dashboard-review-header">

                        <div class="tutor-dashboard-review-heading">
                            <div class="tutor-dashboard-review-title">
								<?php _e('Course: ', 'tutor'); ?>
                                <a href="<?php echo get_the_permalink($review->comment_post_ID); ?>"><?php echo get_the_title($review->comment_post_ID); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="individual-dashboard-review-body">
                        <div class="tutor-bs-d-flex tutor-bs-justify-content-between">
                            <div class="individual-star-rating-wrap">
                                <?php tutor_utils()->star_rating_generator($review->rating); ?>
                                <p class="review-meta">
                                    <?php  echo sprintf(__('%s ago', 'tutor'), human_time_diff(strtotime($review->comment_date)));  ?>
                                </p>
                            </div>
                            <div class="tutor-given-review-action">
                                <span href="javascript:;" data-tutor-modal-target="<?php echo $update_id; ?>">
                                    <i class="ttr-pencil-line"></i> <span><?php _e('Edit', 'tutor'); ?></span>
                                </span>
                                <span href="javascript:;" data-tutor-modal-target="<?php echo $delete_id; ?>">
                                    <i class="ttr-delete-stroke-filled"></i> <span><?php _e('Delete', 'tutor'); ?></span>
                                </span>
                            </div>
                        </div>
						<?php echo wpautop(stripslashes($review->comment_content)); ?>
                    </div>
                </div>

                <!-- Edit Modal -->
                <form class="tutor-modal modal-sticky-header-footer" id="<?php echo $update_id; ?>">
                    <span class="tutor-modal-overlay"></span>
                    <div class="tutor-modal-root">
                        <div class="tutor-modal-inner">
                            <div class="tutor-modal-header">
                                <h3 class="tutor-modal-title">
                                    <?php _e('Update Review'); ?>
                                </h3>
                                <button data-tutor-modal-close class="tutor-modal-close">
                                    <span class="las la-times"></span>
                                </button>
                            </div>
                            
                            <div class="tutor-modal-body-alt modal-container">
                                <input type="hidden" name="review_id" value="<?php echo $review->comment_ID; ?>"/>
                                <div class="tutor-star-rating-container">
                                    <?php
                                        tutor_utils()->star_rating_generator(tutor_utils()->get_rating_value($review->rating));
                                    ?>
                                </div>
                                <textarea class="tutor-form-control" name="review" placeholder="<?php _e('write a review', 'tutor'); ?>"><?php 
                                    echo stripslashes($rating->review); ?>
                                </textarea>
                            </div>

                            <div class="tutor-modal-footer">
                                <div class="tutor-bs-row">
                                    <div class="tutor-bs-col">
                                        <div class="tutor-btn-group">
                                            <button type="submit" data-action="next" class="tutor-btn tutor-is-primary">
                                                <?php _e('Update Review', 'tutor'); ?>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="tutor-bs-col-auto">
                                        <button data-tutor-modal-close type="button" data-action="back" class="tutor-btn tutor-is-default">
                                            <?php _e('Cancel', 'tutor'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Delete Modal -->
                <div id="<?php echo $delete_id; ?>" class="tutor-modal">
                    <span class="tutor-modal-overlay"></span>
                    <button data-tutor-modal-close class="tutor-modal-close">
                        <span class="las la-times"></span>
                    </button>
                    <div class="tutor-modal-root">
                        <div class="tutor-modal-inner">
                            <div class="tutor-modal-body tutor-text-center">
                                <div class="tutor-modal-icon">
                                    <img src="<?php echo tutor()->url; ?>assets/images/icon-trash.svg" />
                                </div>
                                <div class="tutor-modal-text-wrap">
                                    <h3 class="tutor-modal-title">
                                        <?php _e('Delete This Review?', 'tutor'); ?>
                                    </h3>
                                    <p>
                                        <?php _e('Are you sure you want to delete this review permanently from the site? Please confirm your choice.', 'tutor'); ?>
                                    </p>
                                </div>
                                <div class="tutor-modal-btns tutor-btn-group">
                                    <button data-tutor-modal-close class="tutor-btn tutor-is-outline tutor-is-default">
                                        <?php _e('Cancel', 'tutor'); ?>
                                    </button>
                                    <button class="tutor-btn tutor-given-review-delete" data-review-id="<?php echo $review->comment_ID; ?>" data-target-announcement-row-id="<?php echo $row_id; ?>">
                                        <?php _e('Yes, Delete This', 'tutor'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			?>
        </div>
    </div>
</div>