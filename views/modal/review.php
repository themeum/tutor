<?php
    /**
     * 
     * To be loaded after course completion button click
     */
?>
<form class="tutor-modal tutor-modal-is-close-inside-inner tutor-is-active tutor-course-review-popup-form">
    <span class="tutor-modal-overlay"></span>
    <div class="tutor-modal-root">
        <div class="tutor-modal-inner">
            <button data-tutor-modal-close class="tutor-modal-close">
                <span class="tutor-icon-line-cross-line"></span>
            </button>
            <div class="tutor-modal-body tutor-text-center">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>"> 
                <input type="hidden" name="review_id" value="<?php echo isset($review_id) ? $review_id : ''; ?>"/>
                <input type="hidden" name="action" value="tutor_place_rating"/>   

                <div class="tutor-star-rating-container">
                    <h6 class="tutor-fs-4 tutor-fw-normal tutor-color-black-70 tutor-mb-16"><?php _e('How would you rate this course?', 'tutor'); ?></h6>
                    <div class="tutor-modal-text-rating tuor-text-medium-body tutor-color-black tutor-mb-12"><?php _e('Select Rating', 'tutor'); ?></div>

                    <div class="tutor-form-group tutor-stars">
                        <?php
                            tutor_utils()->star_rating_generator(tutor_utils()->get_rating_value());
                        ?>
                    </div>     
                </div>   
                
                <textarea name="review" class="tutor-form-control tutor-mt-28" placeholder="<?php _e('Tell us about your own personal experience taking this course. Was it a good match for you?', 'tutor'); ?>"></textarea>
               
                <div class="tutor-modal-btns tutor-btn-group tutor-mt-32">
                    <button data-tutor-modal-close type="button" class="tutor-modal-close-btn tutor-btn tutor-is-default">
                        <?php _e('Cancel', 'tutor'); ?>
                    </button>
                    <button type="submit" class="tutor-btn tutor_submit_review_btn">
                        <?php _e('Submit', 'tutor'); ?>
                    </button>
                </div>   
            </div>
        </div>
    </div>
</form>