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
?>

<a href="<?php the_permalink(); ?>"> 
    <img src="<?php echo esc_url($tutor_course_img); ?>" alt="Course Thumbnail">
</a>
