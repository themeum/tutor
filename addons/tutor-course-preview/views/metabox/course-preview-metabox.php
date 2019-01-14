<?php

$post_id = get_the_ID();
if ( ! empty($_POST['lesson_id'])){
	$post_id = sanitize_text_field($_POST['lesson_id']);
}


$_is_preview = get_post_meta($post_id, '_is_preview', true);

?>


<div class="tutor-option-field-row">
    <div class="tutor-option-field-label">
        <label for="">
			<?php _e('Enable Course Preview', 'tutor'); ?> <br />
        </label>
    </div>
    <div class="tutor-option-field">

        <label>
            <input type="checkbox" name="_is_preview" value="1" <?php checked(1, $_is_preview); ?> >
            <?php _e('Enable Course Preview', 'tutor'); ?>
        </label>

        <p class="desc">
			<?php _e('If checked, any users/guest can view this lesson without enroll course', 'tutor'); ?>
        </p>
    </div>
</div>