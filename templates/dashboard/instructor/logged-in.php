<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$is_instructor = tutor_utils()->is_instructor();
if ($is_instructor){
    ?>
<style type="text/css">
    .tutor-instructor-pending-content img {
        margin-bottom:69px;
        border-radius: 10px;        
    }
    .tutor-instructor-thankyou-wrapper{
        text-align: center;
    }

    .tutor-instructor-thankyou-text {
        height: 48px;
        line-height: 48px;
        font-size: 40px;
        font-style: normal;
        font-weight: 500;
        letter-spacing: 0px;
        color: #161616;
        margin: 0 auto 26px auto;
    }    
    .tutor-instructor-extra-text {
        height: 54px;
        font-size: 20px;
        font-style: normal;
        font-weight: 400;
        letter-spacing: 0px;
        color: #525252;
        line-height: 30px;
        margin: 0 auto 26px auto;
   
    }
</style>
<div class="tutor-instructor-pending-wrapper">
    <div class="tutor-alert-info tutor-alert">
        <?php _e('With MySpace becoming more popular every day, there is the constant need to be different.','tutor');?>
    </div>
    
    <div class="tutor-instructor-pending-content">
        <img src="<?php echo esc_url(tutor()->url . 'assets/images/new-user.png')?>" alt="<?php _e('New User','tutor')?>">
        <div class="tutor-instructor-thankyou-wrapper">
            <div class="tutor-instructor-thankyou-text">
                <p>
                    <?php _e('Thank you for registering as an instructor!','tutor');?>
                </p>                
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