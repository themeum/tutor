<?php
/**
 * @package TutorLMS/Templates
 * @since 1.6.9
 */

?>

<p><?php _e('Hi,', 'tutor'); ?></p>

<p> 
<?php _e('Instructor <strong>{instructor_name}</strong> added a new topic to {course_name} on <strong>{site_name}</strong>', 'tutor')?>
</p>

<p> <?php _e("Course Link: {course_url}",'tutor')?> </p>
<p> <?php _e("Topic Name: {post_name}",'tutor')?> </p>
<p><?php _e('Reply to this email to communicate with the instructor.', 'tutor'); ?></p>
