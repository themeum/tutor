<h1>Dashboard</h1>

<div class="dozent-dashboard-content-inner">
    <div class='dozent-mycourse-wrap'>
        <?php
        $completed_courses = dozent_utils()->get_completed_courses_ids_by_user();
        $my_courses = dozent_utils()->get_enrolled_courses_by_user();

        $completed_courses_count = count($completed_courses);
        $my_courses_count = $my_courses? $my_courses->post_count : 0;
        $active_course_count = ($my_courses_count - $completed_courses_count);


        echo __('My Course : ', 'dozent') . $my_courses_count . "<br />";
        echo __('Active Course : ', 'dozent') . $active_course_count . "<br />";
        echo __('Complete Course : ', 'dozent') . $completed_courses_count . "<br />";


        ?>

    </div>
</div>