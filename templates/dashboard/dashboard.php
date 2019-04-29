<h3><?php _e('Dashboard', 'tutor') ?></h3>

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

    <div class="tutor-dashboard-info-cards">
        <div class="tutor-dashboard-info-card">
            <p>
                <?php _e('Enrolled Course', 'tutor'); ?>
                <span><?php echo esc_html($enrolled_course_count); ?></span>
            </p>
        </div>
        <div class="tutor-dashboard-info-card">
            <p>
                <?php _e('Active Course', 'tutor'); ?>
                <span><?php echo esc_html($active_course_count); ?></span>
            </p>
        </div>
        <div class="tutor-dashboard-info-card">
            <p>
                <?php _e('Completed Course', 'tutor'); ?>
                <span><?php echo esc_html($completed_course_count); ?></span>
            </p>
        </div>

        <?php
            if(current_user_can(tutor()->instructor_role)) :
        ?>

        <div class="tutor-dashboard-info-card">
            <p>
                <?php _e('Total Students', 'tutor'); ?>
                <span><?php echo esc_html($total_students); ?></span>
            </p>
        </div>
        <div class="tutor-dashboard-info-card">
            <p>
                <?php _e('Total Courses', 'tutor'); ?>
                <span><?php echo esc_html(count($my_courses)); ?></span>
            </p>
        </div>
        <div class="tutor-dashboard-info-card">
            <p>
                <?php _e('Total Earning', 'tutor'); ?>
                <span><?php echo tutor_utils()->tutor_price($earning_sum->instructor_amount); ?></span>
            </p>
        </div>
        <?php
            endif;
        ?>
    </div>

    <div class="tutor-dashboard-info-table-wrap">
        <h3><?php _e('Most Popular Courses', 'tutor'); ?></h3>
        <table class="tutor-dashboard-info-table">
            <thead>
            <tr>
                <td><?php _e('Course Name', 'tutor'); ?></td>
                <td><?php _e('Enrolled', 'tutor'); ?></td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Unlock VS Code: Ultimate Guide To Optimizing Your Workflow</td>
                <td>9</td>
            </tr>
            <tr>
                <td>Unlock VS Code: Ultimate Guide To Optimizing Your Workflow</td>
                <td>9</td>
            </tr>
            <tr>
                <td>Unlock VS Code: Ultimate Guide To Optimizing Your Workflow</td>
                <td>9</td>
            </tr>
            <tr>
                <td>Unlock VS Code: Ultimate Guide To Optimizing Your Workflow</td>
                <td>9</td>
            </tr>
            </tbody>
        </table>
    </div>

</div>