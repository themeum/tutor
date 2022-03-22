<?php
/**
 * Display attachments
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attachments    = tutor_utils()->get_attachments();
$open_mode_view = apply_filters( 'tutor_pro_attachment_open_mode', null ) == 'view' ? ' target="_blank" ' : null;

do_action( 'tutor_global/before/attachments' );

if ( is_array( $attachments ) && count( $attachments ) ) {
	?>
    <div class="tutor-exercise-files tutor-mt-20">
        <?php
            foreach ($attachments as $attachment){
        ?>
        <a href="<?php echo esc_url( $attachment->url ); ?>" <?php echo ($open_mode_view ? $open_mode_view : ' download="'.$attachment->name.'" ' ); ?>>
            <div class="tutor-instructor-card tutor-mb-12">
                <div class="tutor-icard-content">
                    <h6 class="tutor-name tutor-fs-6 tutor-color-black-70">
                        <?php echo esc_html( $attachment->name ); ?>
                    </h6>
                    <div class="tutor-fs-7">
                        <?php echo esc_html( $attachment->size ); ?>
                    </div>
                </div>
                <div class="tutor-avatar tutor-is-xs flex-center tutor-flex-shrink-0">
                    <span class="tutor-icon-24 tutor-icon-download-line tutor-color-design-brand"></span>
                </div>
            </div>
        </a>
        <?php } ?>
    </div>
<?php } else {
    tutor_utils()->tutor_empty_state(__('No Attachment Found', 'tutor'));
}

do_action( 'tutor_global/after/attachments' ); ?>
