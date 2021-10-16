
<?php
    $course_id = get_the_ID();
    $levels = tutor_utils()->course_levels();
    $course_level = get_post_meta($course_id, '_tutor_course_level', true);
?>
<div class="tutor-row">
    <div class="tutor-col-4">
        <label for="">
            <?php _e('Difficulty Level', 'tutor'); ?> <br />
        </label>
    </div>
    <div class="tutor-col-8">
        <select name="course_level" class="tutor-form-select">
            <?php
            foreach ($levels as $level_key => $level){
                ?>
                    <option value="<?php echo $level_key; ?>" <?php ($course_level ? selected($level_key, $course_level) : $level_key === 'intermediate') ? selected(1, 1): ''; ?>> 
                        <?php echo $level; ?>
                    </option>
                <?php
            }
            ?>
        </select>
    </div>
</div>