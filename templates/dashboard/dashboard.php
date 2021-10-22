<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<h3><?php _e('Dashboard', 'tutor') ?></h3>

<div class="tutor-dashboard-content-inner">

	<?php
	$enrolled_course = tutor_utils()->get_enrolled_courses_by_user();
	$completed_courses = tutor_utils()->get_completed_courses_ids_by_user();
	$total_students = tutor_utils()->get_total_students_by_instructor(get_current_user_id());
	$my_courses = tutor_utils()->get_courses_by_instructor(get_current_user_id(), 'publish');
	$earning_sum = tutor_utils()->get_earning_sum();

	$enrolled_course_count = $enrolled_course ? $enrolled_course->post_count : 0;
	$completed_course_count = count($completed_courses);
    $active_course_count = $enrolled_course_count - $completed_course_count;
    $active_course_count<0 ? $active_course_count=0 : 0;
    
    $status_translations = array(
        'publish' => __('Published', 'tutor'),
        'pending' => __('Pending', 'tutor'),
        'trash' => __('Trash', 'tutor')
    );
    
	?>

    <div class="tutor-bs-row tutor-dashboard-cards-container">
        <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
            <p>
                <span class="tutor-round-icon">
                    <i class="ttr ttr-book-open-filled"></i>
                </span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($enrolled_course_count); ?></span>
                <span><?php _e('Enrolled Courses', 'tutor'); ?></span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($enrolled_course_count); ?></span>
            </p>
        </div>
        <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
            <p>
                <span class="tutor-round-icon">
                    <i class="ttr ttr-college-graduation-filled"></i>
                </span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($active_course_count); ?></span>
                <span><?php _e('Active Courses', 'tutor'); ?></span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($active_course_count); ?></span>
            </p>
        </div>
        <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
            <p>
                <span class="tutor-round-icon">
                    <i class="ttr ttr-award-filled"></i>
                </span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($completed_course_count); ?></span>
                <span><?php _e('Completed Courses', 'tutor'); ?></span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($completed_course_count); ?></span>
            </p>
        </div>

		<?php
		if(current_user_can(tutor()->instructor_role)) :
			?>
            <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
                <p>
                    <span class="tutor-round-icon">
                        <i class="ttr ttr-user-graduate-filled"></i>
                    </span>
                    <span class="tutor-dashboard-info-val"><?php echo esc_html($total_students); ?></span>
                    <span><?php _e('Total Students', 'tutor'); ?></span>
                    <span class="tutor-dashboard-info-val"><?php echo esc_html($total_students); ?></span>
                </p>
            </div>
            <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
                <p>
                    <span class="tutor-round-icon">
                        <i class="ttr ttr-box-open-filled"></i>
                    </span>
                    <span class="tutor-dashboard-info-val"><?php echo esc_html(count($my_courses)); ?></span>
                    <span><?php _e('Total Courses', 'tutor'); ?></span>
                    <span class="tutor-dashboard-info-val"><?php echo esc_html(count($my_courses)); ?></span>
                </p>
            </div>
            <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
                <p>
                    <span class="tutor-round-icon">
                        <i class="ttr ttr-coins-filled"></i>
                    </span>
                    <span class="tutor-dashboard-info-val"><?php echo tutor_utils()->tutor_price($earning_sum->instructor_amount); ?></span>
                    <span><?php _e('Total Earnings', 'tutor'); ?></span>
                    <span class="tutor-dashboard-info-val"><?php echo tutor_utils()->tutor_price($earning_sum->instructor_amount); ?></span>
                </p>
            </div>
		<?php
		endif;
		?>
    </div>
</div>

<?php
$instructor_course = tutor_utils()->get_courses_for_instructors(get_current_user_id());

if(count($instructor_course)) {
    $course_badges = array(
        'publish' => 'success',
        'pending' => 'warning',
        'trash' => 'danger'
    );

    ?>
        <h3 class="popular-courses-heading-dashboard">
            <?php _e('Popular Courses', 'tutor'); ?>
            <a style="float:right" class="tutor-view-all-course" href="<?php echo tutor_utils()->tutor_dashboard_url('my-courses'); ?>">
                <?php _e('View All', 'tutor'); ?>
            </a>
        </h3>
        <div class="tutor-dashboard-content-inner">
            <table class="tutor-ui-table tutor-ui-table-responsive table-popular-courses">
                <thead>
                    <tr>
                        <th>
                            <span class="text-regular-small color-text-subsued">
                                <?php _e('Course Name', 'tutor'); ?>
                            </span>
                        </th>
                        <th>
                            <div class="inline-flex-center color-text-subsued">
                                <span class="text-regular-small"><?php _e('Enrolled', 'tutor'); ?></span>
                                <span class="tutor-v2-icon-test icon-ordering-a-to-z-filled"></span>
                            </div>
                        </th>
                        <th>
                            <div class="inline-flex-center color-text-subsued">
                                <span class="text-regular-small"><?php _e('Rating', 'tutor'); ?></span>
                                <span class="tutor-v2-icon-test icon-ordering-a-to-z-filled"></span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($instructor_course as $course){
                        $enrolled = tutor_utils()->count_enrolled_users_by_course($course->ID);
                        $course_status = isset($status_translations[$course->post_status]) ? $status_translations[$course->post_status] : __($course->post_status, 'tutor');
                        $course_rating = tutor_utils()->get_course_rating($course->ID);
                        $course_badge =  isset($course_badges[$course->post_status]) ? $course_badges[$course->post_status] : 'dark';
                        
                        ?>
                        <tr>
                            <td data-th="<?php _e('Course Name', 'tutor'); ?>" class="column-fullwidth">
                                <div class="td-course text-medium-body color-text-primary">
                                    <a href="<?php echo get_the_permalink($course->ID); ?>" target="_blank">
                                        <?php echo $course->post_title; ?>
                                    </a>
                                </div>
                            </td>
                            <td data-th="<?php _e('Enrolled', 'tutor'); ?>">
                                <span class="text-medium-caption color-text-primary">
                                    <?php echo $enrolled; ?>
                                </span>
                            </td>
                            <td data-th="<?php _e('Rating', 'tutor'); ?>">
                                <div class="td-tutor-rating text-regular-body color-text-subsued">
                                    <?php tutor_utils()->star_rating_generator($course_rating->rating_avg); ?> <span><?php echo $course_rating->rating_avg; ?></span>
                                </div>
                            </td>
                        </tr>
                        <?php
                    } ?>
                </tbody>
            </table>
        </div>
        <?php 
    } 
?>