<?php
/**
 * Display attachments
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

$attachments = lms_utils()->get_attachments();

do_action('lms_global/before/attachments');

if (is_array($attachments) && count($attachments)){
	?>
    <div class="lms-page-segment lms-attachments-wrap">
        <h3><?php _e('Attachments', 'lms'); ?></h3>
        <?php
        foreach ($attachments as $attachment){
            ?>
            <a href="<?php echo $attachment->url; ?>" class="lms-lesson-attachment clearfix">
                <div class="lms-attachment-icon">
                    <img src="<?php echo $attachment->icon; ?>" />
                </div>

                <div class="lms-attachment-info">
                    <p><?php echo $attachment->name; ?></p>
                    <span><?php echo $attachment->size; ?></span>
                </div>
            </a>
            <?php
        }
        ?>
    </div>
<?php }

do_action('lms_global/after/attachments'); ?>