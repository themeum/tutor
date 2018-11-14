<div class="tutor-loop-course-footer">
    <?php
        $course_duration = get_tutor_course_duration_context();
        $course_students = tutor_utils()->get_total_students();
        # @TODO: Need Two Icon
        if(!empty($course_duration)) echo "<i class='icon-star-empty'></i> $course_duration";
        if(!empty($course_students)) echo "<i class='icon-star'></i> $course_students";
        tutor_course_loop_price();
        tutor_course_loop_add_to_cart();
    ?>
</div>
