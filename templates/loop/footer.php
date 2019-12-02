<div class="tutor-loop-course-footer">
    <?php
        $course_duration = get_tutor_course_duration_context();
        $course_students = tutor_utils()->count_enrolled_users_by_course();
        if(!empty($course_duration)) echo "<i class='tutor-icon-clock'></i> <span>$course_duration</span>";
        if(!empty($course_students)) echo "<i class='tutor-icon-user'></i> <span>$course_students</span>";
        tutor_course_loop_price();
    ?>
</div>
