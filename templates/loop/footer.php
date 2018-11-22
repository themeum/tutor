<div class="dozent-loop-course-footer">
    <?php
        $course_duration = get_dozent_course_duration_context();
        $course_students = dozent_utils()->get_total_students();
        if(!empty($course_duration)) echo "<i class='dozent-icon-clock'></i> <span>$course_duration</span>";
        if(!empty($course_students)) echo "<i class='dozent-icon-user'></i> <span>$course_students</span>";
        dozent_course_loop_price();
    ?>
</div>
