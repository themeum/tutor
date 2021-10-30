<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<div class="tutor-course-listing-item-footer <?php if (tutor_utils()->is_course_purchasable()) {echo"has-border";}else{echo"no-border";} ?> tutor-py-15 tutor-px-20">
    <?php  tutor_course_loop_price(); ?>
</div>
