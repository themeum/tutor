<?php
    /**
     * 
     * To be loaded after course completion button click
     */
?>
<form class="tutor-modal tutor-is-active tutor-course-review-popup-form">
    <span class="tutor-modal-overlay"></span>
    <button data-tutor-modal-close class="tutor-modal-close">
        <span class="las la-times"></span>
    </button>
    <div class="tutor-modal-root">
        <div class="tutor-modal-inner">
            <div class="tutor-modal-body tutor-text-center">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>"> 
                <input type="hidden" name="review_id" value=""/>
                <input type="hidden" name="action" value="tutor_place_rating"/>   

                <div class="tutor-star-rating-container">
                    <h6 class="text-bold-h6"><?php _e('How would you rate this course?', 'tutor'); ?></h6>
                    <div class="text-regular-caption tutor-mb-10"><?php _e('Select Rating', 'tutor'); ?></div>

                    <div class="tutor-form-group tutor-stars">
                        <?php
                            tutor_utils()->star_rating_generator(tutor_utils()->get_rating_value());
                        ?>
                    </div>     
                </div>   
                
                <textarea name="review" class="tutor-form-control" placeholder="<?php _e('Tell us about your own personal experience taking this course. Was it a good match for you?', 'tutor'); ?>"></textarea>
               
                <div class="tutor-modal-btns tutor-btn-group tutor-mt-30">
                    <button data-tutor-modal-close type="button" class="tutor-btn tutor-is-default">
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