<h1><?php _e('Dashboard', 'tutor') ?></h1>

<div class="tutor-dashboard-content-inner">

    <?php
        $enrolled_course = tutor_utils()->get_enrolled_courses_by_user();
        $completed_courses = tutor_utils()->get_completed_courses_ids_by_user();
        $total_students = tutor_utils()->get_total_students_by_instructor(get_current_user_id());
        $my_courses = tutor_utils()->get_courses_by_instructor(get_current_user_id(), 'any');
        $earning_sum = tutor_utils()->get_earning_sum();

        $enrolled_course_count = $enrolled_course->post_count;
        $completed_course_count = count($completed_courses);
        $active_course_count = $enrolled_course_count - $completed_course_count;

    ?>

    <ul class="tutor-dashboard-info-cards">
        <li>
            <p>
                <?php _e('Enrolled Course', 'tutor'); ?>
                <span><?php echo esc_html($enrolled_course_count); ?></span>
            </p>
        </li>
        <li>
            <p>
                <?php _e('Active Course', 'tutor'); ?>
                <span><?php echo esc_html($active_course_count); ?></span>
            </p>
        </li>
        <li>
            <p>
                <?php _e('Completed Course', 'tutor'); ?>
                <span><?php echo esc_html($completed_course_count); ?></span>
            </p>
        </li>

        <?php
            if(current_user_can(tutor()->instructor_role)) :
        ?>

        <li>
            <p>
                <?php _e('Total Students', 'tutor'); ?>
                <span><?php echo esc_html($total_students); ?></span>
            </p>
        </li>
        <li>
            <p>
                <?php _e('Total Courses', 'tutor'); ?>
                <span><?php echo esc_html(count($my_courses)); ?></span>
            </p>
        </li>
        <li>
            <p>
                <?php _e('Total Earning', 'tutor'); ?>
                <span><?php echo tutor_utils()->tutor_price($earning_sum->instructor_amount); ?></span>
            </p>
        </li>
        <?php
            endif;
        ?>
    </ul>
</div>