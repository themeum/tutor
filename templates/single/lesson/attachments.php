<?php

/**
 * Display Video
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

$attachments = lms_utils()->get_lesson_attachments();
?>

<?php do_action('lms_lesson/single/before/attachments'); ?>

<?php
if (is_array($attachments) && count($attachments)){
	?>
    <div class="lms-single-lesson-segment lms-lesson-attachments-wrap">

        <?php

        foreach ($attachments as $attachment){
            ?>
            <a href="<?php echo $attachment->url; ?>" class="lms-lesson-attachment clearfix">
                <div class="lms-lesson-icon">
                    <img src="<?php echo $attachment->icon; ?>" />
                </div>

                <div class="lms-lesson-info">
                    <p><?php echo $attachment->name; ?></p>
                    <span><?php echo $attachment->size; ?></span>
                </div>
            </a>
            <?php
        }


        ?>

    </div>
<?php } ?>

<?php do_action('lms_lesson/single/after/attachments'); ?>