<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$is_instructor = tutor_utils()->is_instructor();
if ($is_instructor){
    ?>

<div class="tutor-instructor-pending-wrapper">
    <div class="tutor-alert-info tutor-alert">
        <?php _e('With MySpace becoming more popular every day, there is the constant need to be different.','tutor');?>
    </div>
    
    <div class="tutor-instructor-pending-content">
        <img src="<?php echo esc_url(tutor()->url . 'assets/images/new-user.png')?>" alt="<?php _e('New User','tutor')?>">
        <div class="tutor-instructor-thankyou-wrapper">
            <div class="tutor-instructor-thankyou-text">
                <h2>
                    <?php _e('Thank you for registering as an instructor!','tutor');?>
                </h2>                
            </div>
            <div class="tutor-instructor-extra-text">
                <p>
                    <?php _e('We\'ve received your application, and we will review it soon. Please hang tight!','tutor');?>
                </p>                
            </div>

            <a class="tutor-button" href="<?= esc_url(tutor_utils()->tutor_dashboard_url())?>">
                <?php _e('Go to Dashboard','tutor');?>
            </a>
        </div>
    </div>
</div>

<?php }
else{
    tutor_load_template('dashboard.instructor.apply_for_instructor');
} ?>