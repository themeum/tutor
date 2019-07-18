<?php
/**
 * Don't change it, it's supporting modal in other place
 * if get_the_ID() empty, then it's means we are passing $post variable from another place
 */
if (get_the_ID())
    global $post;

$video = maybe_unserialize(get_post_meta($post->ID, '_video', true));

$videoSource = tutor_utils()->avalue_dot('source', $video);
$runtimeHours = tutor_utils()->avalue_dot('runtime.hours', $video);
$runtimeMinutes = tutor_utils()->avalue_dot('runtime.minutes', $video);
$runtimeSeconds = tutor_utils()->avalue_dot('runtime.seconds', $video);
$sourceVideoID = tutor_utils()->avalue_dot('source_video_id', $video);
$poster = tutor_utils()->avalue_dot('poster', $video);
?>
<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for="">
            <?php
            if ($post->post_type === tutor()->course_post_type){
	            _e('Course Intro Video', 'tutor');
            }else{
	            _e('Video Source', 'tutor');
            }
            ?>
        </label>
    </div>
    <div class="tutor-option-field">
        <select name="video[source]" class="tutor_lesson_video_source tutor_select2">
            <option value=""><?php _e('Select Video Source', 'tutor'); ?></option>
            <option value="html5" <?php selected('html5', $videoSource); ?> ><?php _e('HTML5 (mp4)', 'tutor'); ?></option>
            <option value="external_url" <?php selected('external_url', $videoSource); ?>><?php _e('External URL', 'tutor'); ?></option>
            <option value="youtube" <?php selected('youtube', $videoSource); ?>><?php _e('YouTube', 'tutor'); ?></option>
            <option value="vimeo" <?php selected('vimeo', $videoSource); ?>><?php _e('Vimeo', 'tutor'); ?></option>
        </select>

        <p class="desc">
			<?php _e('Select the video type and place video value below.', 'tutor'); ?>
        </p>

        <div class="tutor-lesson-video-input">
            <div class="video_source_wrap_html5"  style="display: <?php echo $videoSource === 'html5' ? 'block' : 'none'; ?>;">
                <a href="javascript:;" class="video_upload_btn"><i class="dashicons dashicons-upload"></i> </a>
                <input type="hidden" name="video[source_video_id]" value="<?php echo $sourceVideoID; ?>" >
                <p style="display: <?php echo $sourceVideoID ? 'block' : 'none'; ?>;"><?php _e('Media ID', 'tutor'); ?>: <span class="video_media_id"><?php echo $sourceVideoID; ?></span></p>
            </div>

            <div class="video_source_wrap_external_url" style="display: <?php echo $videoSource === 'external_url' ? 'block' :
		        'none'; ?>;">
                <input type="text" name="video[source_external_url]" value="<?php echo tutor_utils()->avalue_dot('source_external_url', $video);
                ?>" placeholder="<?php _e('External Video URL', 'tutor'); ?>">
            </div>

            <div class="video_source_wrap_youtube" style="display: <?php echo $videoSource === 'youtube' ? 'block' :
				'none'; ?>;">
                <input type="text" name="video[source_youtube]" value="<?php echo tutor_utils()->avalue_dot('source_youtube', $video); ?>" placeholder="<?php _e('YouTube Video URL', 'tutor'); ?>">
            </div>
            
            
            <div class="video_source_wrap_vimeo" style="display: <?php echo $videoSource === 'vimeo' ? 'block' : 'none'; ?>;">
                <input type="text" name="video[source_vimeo]" value="<?php echo tutor_utils()->avalue_dot('source_vimeo', $video); ?>" placeholder="<?php _e('Vimeo Video URL', 'tutor'); ?>">
            </div>
        </div>

    </div>
</div>

<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for=""><?php _e('Video Run Time', 'tutor'); ?></label>
    </div>
    <div class="tutor-option-field">
        <div class="tutor-option-gorup-fields-wrap">
            <div class="tutor-lesson-video-runtime">
                <div class="tutor-option-group-field">
                    <input type="text" value="<?php echo $runtimeHours ? $runtimeHours : '00'; ?>" name="video[runtime][hours]">
                    <p class="desc"><?php _e('HH', 'tutor'); ?></p>
                </div>

                <div class="tutor-option-group-field">
                    <input type="text" value="<?php echo $runtimeMinutes ? $runtimeMinutes : '00'; ?>" name="video[runtime][minutes]">
                    <p class="desc"><?php _e('MM', 'tutor'); ?></p>
                </div>

                <div class="tutor-option-group-field">
                    <input type="text" value="<?php echo $runtimeSeconds ? $runtimeSeconds : '00'; ?>" name="video[runtime][seconds]">
                    <p class="desc"><?php _e('SS', 'tutor'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tutor-option-field-row tutor-video-poster-field" style="display: <?php echo $videoSource === 'html5' ? 'block': 'none'; ?>;">
    <div class="tutor-option-field-label">
        <label for=""><?php _e('Video Poster', 'tutor'); ?></label>
    </div>
    <div class="tutor-option-field">
        <div class="tutor-option-gorup-fields-wrap">
            <div class="tutor-video-poster-wrap">
                <p class="video-poster-img">
					<?php
					if ($poster){
						echo '<img src="'.wp_get_attachment_image_url($poster).'" alt="" /> ';
					}
					?>
                </p>
                <input type="hidden" name="video[poster]" value="<?php echo $poster; ?>">
                <button type="button" class="tutor_video_poster_upload_btn tutor-btn"><i class="tutor-icon-checkbox-pen-outline"></i><?php _e('Upload', 'tutor'); ?></button>
            </div>

        </div>
    </div>
</div>