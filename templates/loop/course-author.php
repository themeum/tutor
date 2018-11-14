<?php

/**
 * Display loop thumbnail
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

global $post;
?>

<div class="tutor-loop-author">
    <div class="tutor-single-course-avatar">
        <?php echo tutor_utils()->get_tutor_avatar($post->post_author); ?>
    </div>
    <div class="tutor-single-course-author-name">
        <h6><?php _e('by', 'tutor'); ?></h6>
        <?php echo get_tutor_course_author(); ?>
    </div>
    <div class="tutor-course-lising-category">
        <?php
            $course_categories = get_tutor_course_categories();
            if(!empty($course_categories) && is_array($course_categories ) && count($course_categories)){
                ?>
                <h6><?php esc_html_e('In', 'tutor') ?></h6>
                <?php
                foreach ($course_categories as $course_category){
                    $category_name = $course_category->name;
                    echo "<span>$category_name</span>";
                }
            }
        ?>
    </div>
</div>
