<?php
/**
 * Display Permission denied
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 *
 * Template content and design updated
 *
 * @version 1.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

tutor_utils()->tutor_custom_header();

?>

<div class="tutor-wrap tutor-wrap-parent tutor-page-permission-denied">
    <div class="tutor-container">
        <div class="tutor-row tutor-justify-center">
            <div class="tutor-col-md-8 tutor-col-lg-6 tutor-col-xl-5">
                <div class="tutor-card">
                    <div class="tutor-px-40 tutor-py-64 tutor-text-center">
                        <div class="tutor-svg-img">
                            <img src="<?php echo esc_url( tutor()->url.'assets/images/permission-denied.svg' ); ?>" alt="<?php _e("Permission Denied", "tutor"); ?>">    
                        </div>

                        <div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-n32 tutor-mb-12"><?php echo isset($message) ? $message : __( 'You don\'t have permission to access this page', 'tutor' ); ?></div>
                        <div class="tutor-fs-6 tutor-color-muted tutor-mb-36"><?php echo isset($description) ? $description : __('Please make sure you are logged in to correct account if the content needs authorization.', 'tutor'); ?></div>

                        <?php 
                            if(!isset($button)) {
                                $button = array(
                                    'url' => get_home_url(),
                                    'text' => 'Homepage'
                                );
                            }
                        ?>
                        <a href="<?php echo $button['url']; ?>" class="tutor-btn tutor-btn-primary">
                            <?php echo $button['text']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
tutor_utils()->tutor_custom_footer();
