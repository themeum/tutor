<?php
global $post;
$video = maybe_unserialize(get_post_meta(get_the_ID(), '_video', true));

$videoSource = dozent_utils()->avalue_dot('source', $video);
$runtimeHours = dozent_utils()->avalue_dot('runtime.hours', $video);
$runtimeMinutes = dozent_utils()->avalue_dot('runtime.minutes', $video);
$runtimeSeconds = dozent_utils()->avalue_dot('runtime.seconds', $video);
$sourceVideoID = dozent_utils()->avalue_dot('source_video_id', $video);
$poster = dozent_utils()->avalue_dot('poster', $video);
?>

<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for="">
            <?php
            if ($post->post_type === dozent()->course_post_type){
	            _e('Course Intro Video', 'dozent');
            }else{
	            _e('Video Source', 'dozent');
            }
            ?>
        </label>
    </div>
    <div class="dozent-option-field">
        <select name="video[source]" class="dozent_lesson_video_source dozent_select2">
            <option value=""><?php _e('Select Video Source', 'dozent'); ?></option>
            <option value="html5" <?php selected('html5', $videoSource); ?> ><?php _e('HTML5 (mp4)', 'dozent'); ?></option>
            <option value="youtube" <?php selected('youtube', $videoSource); ?>><?php _e('YouTube', 'dozent'); ?></option>
            <option value="vimeo" <?php selected('vimeo', $videoSource); ?>><?php _e('Vimeo', 'dozent'); ?></option>
        </select>

        <p class="desc">
			<?php _e('Select the video type and place video value below.', 'dozent'); ?>
        </p>

        <div class="dozent-lesson-video-input">
            <div class="video_source_wrap_html5"  style="display: <?php echo $videoSource === 'html5' ? 'block' : 'none'; ?>;">
                <a href="javascript:;" class="video_upload_btn"><i class="dashicons dashicons-upload"></i> </a>
                <input type="hidden" name="video[source_video_id]" value="<?php echo $sourceVideoID; ?>" >
                <p style="display: <?php echo $sourceVideoID ? 'block' : 'none'; ?>;"><?php _e('Media ID', 'dozent'); ?>: <span class="video_media_id"><?php echo $sourceVideoID; ?></span></p>
            </div>

            <div class="video_source_wrap_youtube" style="display: <?php echo $videoSource === 'youtube' ? 'block' :
				'none'; ?>;">
                <input type="text" name="video[source_youtube]" value="<?php echo dozent_utils()->avalue_dot('source_youtube', $video); ?>" placeholder="<?php _e('YouTube Video URL', 'dozent'); ?>">
            </div>
            <div class="video_source_wrap_vimeo" style="display: <?php echo $videoSource === 'vimeo' ? 'block' : 'none'; ?>;">
                <input type="text" name="video[source_vimeo]" value="<?php echo dozent_utils()->avalue_dot('source_vimeo', $video); ?>" placeholder="<?php _e('Vimeo Video URL', 'dozent'); ?>">
            </div>
        </div>

    </div>
</div>

<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for=""><?php _e('Video Run Time', 'dozent'); ?></label>
    </div>
    <div class="dozent-option-field">
        <div class="dozent-option-gorup-fields-wrap">
            <div class="dozent-lesson-video-runtime">
                <div class="dozent-option-group-field">
                    <input type="text" value="<?php echo $runtimeHours ? $runtimeHours : '00'; ?>" name="video[runtime][hours]">
                    <p><?php _e('HH', 'dozent'); ?></p>
                </div>

                <div class="dozent-option-group-field">
                    <input type="text" value="<?php echo $runtimeMinutes ? $runtimeMinutes : '00'; ?>" name="video[runtime][minutes]">
                    <p><?php _e('MM', 'dozent'); ?></p>
                </div>

                <div class="dozent-option-group-field">
                    <input type="text" value="<?php echo $runtimeSeconds ? $runtimeSeconds : '00'; ?>" name="video[runtime][seconds]">
                    <p><?php _e('SS', 'dozent'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="dozent-option-field-row">
    <div class="dozent-option-field-label">
        <label for=""><?php _e('Video Poster', 'dozent'); ?></label>
    </div>
    <div class="dozent-option-field">
        <div class="dozent-option-gorup-fields-wrap">
            <div class="dozent-video-poster-wrap">
                <p class="video-poster-img">
					<?php
					if ($poster){
						echo '<img src="'.wp_get_attachment_image_url($poster).'" alt="" /> ';
					}
					?>
                </p>
                <input type="hidden" name="video[poster]" value="<?php echo $poster; ?>">
                <button type="button" class="dozent_video_poster_upload_btn button button-primary"><?php _e('Upload', 'dozent'); ?></button>
            </div>

        </div>
    </div>
</div>