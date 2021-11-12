<?php

/**
 * Display loop thumbnail
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $post, $authordata;

$profile_url = tutor_utils()->profile_url($authordata->ID);
?>

<div class="list-item-author tutor-bs-d-flex tutor-bs-align-items-center tutor-mt-30">
	<div class="tutor-avatar">
		<a href="<?php echo esc_url($profile_url); ?>"> 
            <?php echo wp_kses_post(tutor_utils()->get_tutor_avatar($post->post_author)); ?>
        </a>
	</div>
	<div class="text-regular-caption color-text-subsued">
		<?php esc_html_e('By', 'tutor') ?>
		<span class="text-medium-caption color-text-primary">
		<?php esc_html_e(get_the_author()); ?>
		</span>
		<?php
            $course_categories = get_tutor_course_categories();
            if(!empty($course_categories) && is_array($course_categories ) && count($course_categories)){
        ?>
        <?php esc_html_e('In', 'tutor') ?>
		<span class="text-medium-caption course-category color-text-primary">
        <?php
            foreach ($course_categories as $course_category){
                $category_name = $course_category->name;
                $category_link = get_term_link($course_category->term_id);
                echo wp_kses_post("<a href='$category_link'>$category_name </a>");
            }
        }
        ?>
		</span>
	</div>
</div>
