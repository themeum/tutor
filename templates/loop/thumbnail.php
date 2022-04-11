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

$tutor_course_img = get_tutor_course_thumbnail_src();
$placeholder_img = tutor()->url . 'assets/images/placeholder.png';
?>

<a href="<?php the_permalink(); ?>" class="tutor-course-listing-thumb-permalink">
    <div class="tutor-course-listing-thumbnail tutor-ratio tutor-ratio-16x9">
        <img src="<?php echo empty(esc_url($tutor_course_img)) ? $placeholder_img : esc_url($tutor_course_img) ?>" alt="<?php the_title(); ?>" loading="lazy">
    </div>
</a>
