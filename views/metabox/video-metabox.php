<?php
$video = maybe_unserialize(get_post_meta(get_the_ID(), '_video', true));

$videoSource = lms_utils()->avalue_dot('source', $video);
$runtimeHours = lms_utils()->avalue_dot('runtime.hours', $video);
$runtimeMinutes = lms_utils()->avalue_dot('runtime.minutes', $video);
$runtimeSeconds = lms_utils()->avalue_dot('runtime.seconds', $video);
$sourceVideoID = lms_utils()->avalue_dot('source_video_id', $video);
$poster = lms_utils()->avalue_dot('poster', $video);
?>

<div class="lms-option-field-row">
    <div class="lms-option-field-label">
        <label for=""><?php _e('Video Source', 'lms'); ?></label>
    </div>
    <div class="lms-option-field">

        <select name="video[source]" class="lms_lesson_video_source select2">
            <option value=""><?php _e('Select Video Source', 'lms'); ?></option>
            <option value="self_hosted" <?php selected('self_hosted', $videoSource); ?> ><?php _e('Self Hosted, WordPress Media', 'lms'); ?></option>
            <option value="youtube" <?php selected('youtube', $videoSource); ?>><?php _e('YouTube', 'lms'); ?></option>
            <option value="vimeo" <?php selected('vimeo', $videoSource); ?>><?php _e('Vimeo', 'lms'); ?></option>
        </select>

        <p class="desc">
			<?php _e('Select the video type and place video value below.', 'lms'); ?>
        </p>

        <div class="lms-lesson-video-input">

            <div class="video_source_wrap_self_hosted"  style="display: <?php echo $videoSource === 'self_hosted' ? 'block' : 'none'; ?>;">
                <a href="javascript:;" class="video_upload_btn"><i class="dashicons dashicons-upload"></i> </a>
                <input type="hidden" name="video[source_video_id]" value="<?php echo $sourceVideoID; ?>" >
                <p style="display: <?php echo $sourceVideoID ? 'block' : 'none'; ?>;"><?php _e('Media ID', 'lms'); ?>: <span class="video_media_id"><?php echo $sourceVideoID;
						?></span></p>
            </div>

            <div class="video_source_wrap_youtube" style="display: <?php echo $videoSource === 'youtube' ? 'block' :
				'none'; ?>;">
                <input type="text" name="video[source_youtube]" value="<?php echo lms_utils()->avalue_dot('source_youtube', $video); ?>" placeholder="<?php _e('YouTube Video URL', 'lms'); ?>">
            </div>
            <div class="video_source_wrap_vimeo" style="display: <?php echo $videoSource === 'vimeo' ? 'block' : 'none'; ?>;">
                <input type="text" name="video[source_vimeo]" value="<?php echo lms_utils()->avalue_dot('source_vimeo', $video); ?>" placeholder="<?php _e('Vimeo Video URL', 'lms'); ?>">
            </div>
        </div>

    </div>
</div>

<div class="lms-option-field-row">
    <div class="lms-option-field-label">
        <label for=""><?php _e('Video Run Time', 'lms'); ?></label>
    </div>
    <div class="lms-option-field">

        <div class="lms-option-gorup-fields-wrap">
            <div class="lms-lesson-video-runtime">

                <div class="lms-option-group-field">
                    <input type="text" value="<?php echo $runtimeHours ? $runtimeHours : '00'; ?>" name="video[runtime][hours]">
                    <p><?php _e('HH', 'lms'); ?></p>
                </div>
                <div class="lms-option-group-field">
                    <input type="text" value="<?php echo $runtimeMinutes ? $runtimeMinutes : '00'; ?>" name="video[runtime][minutes]">
                    <p><?php _e('MM', 'lms'); ?></p>
                </div>

                <div class="lms-option-group-field">
                    <input type="text" value="<?php echo $runtimeSeconds ? $runtimeSeconds : '00'; ?>" name="video[runtime][seconds]">
                    <p><?php _e('SS', 'lms'); ?></p>
                </div>

            </div>
        </div>

    </div>
</div>

<div class="lms-option-field-row">
    <div class="lms-option-field-label">
        <label for=""><?php _e('Video Poster', 'lms'); ?></label>
    </div>
    <div class="lms-option-field">
        <div class="lms-option-gorup-fields-wrap">
            <div class="lms-video-poster-wrap">
                <p class="video-poster-img">
					<?php
					if ($poster){
						echo '<img src="'.wp_get_attachment_image_url($poster).'" alt="" /> ';
					}
					?>
                </p>
                <input type="hidden" name="video[poster]" value="<?php echo $poster; ?>">
                <button type="button" class="lms_video_poster_upload_btn button button-primary"><?php _e('Upload', 'lms'); ?></button>
            </div>

        </div>
    </div>
</div>