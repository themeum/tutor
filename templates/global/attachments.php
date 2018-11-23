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

$attachments = dozent_utils()->get_attachments();
do_action('dozent_global/before/attachments');

if (is_array($attachments) && count($attachments)){
	?>
    <div class="dozent-page-segment dozent-attachments-wrap">
        <h3><?php _e('Attachments', 'dozent'); ?></h3>
        <?php
        foreach ($attachments as $attachment){
            ?>
            <a href="<?php echo $attachment->url; ?>" class="dozent-lesson-attachment clearfix">
                <div class="dozent-attachment-icon">
                    <i class="dozent-icon-<?php echo $attachment->icon; ?>"></i>
                </div>
                <div class="dozent-attachment-info">
                    <span><?php echo $attachment->name; ?></span>
                    <span><?php echo $attachment->size; ?></span>
                </div>
            </a>
            <?php
        }
        ?>
    </div>
<?php }

do_action('dozent_global/after/attachments'); ?>