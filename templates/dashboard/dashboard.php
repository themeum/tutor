<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>
<?php

if(tutor_utils()->get_option('enable_profile_completion')) {
    $profile_completion = tutor_utils()->user_profile_completion();
    $total_count = count($profile_completion);
    $incomplete_count = count(array_filter($profile_completion, function($data){return !$data['is_set'];}));
    $complete_count = $total_count-$incomplete_count;

    if($total_count && $incomplete_count && $incomplete_count<$total_count) {
        ?>
        <div class="profile-completion">
            <div class="tutor-bs-row tutor-bs-align-items-center">
                <div class="tutor-bs-col-md-8 profile-completion-content">
                    <div class="list-item-title text-medium-h5 color-text-primary">
                        <?php esc_html_e( 'Complete Your Profile', 'tutor' ) ?>
                    </div>
                    <div class="tutor-mt-20">
                        <?php 
                            for($i=1; $i<=$total_count; $i++) {
                                $class = $i > $complete_count ?
                                            'tutor-btn tutor-btn-sm tutor-btn-disable tutor-no-hover tutor-btn-full' :
                                            'tutor-btn tutor-btn-sm tutor-btn-full'
                                ?>
                                <li>
                                    <button class="<?php echo $class; ?>"></button>
                                </li>
                                <?php
                            }
                        ?>
                        <li>
                            <span class="tutor-round-icon">
                                <i class="ttr-award-filled"></i>
                            </span>
                        </li>
                    </div>
                    <div class="list-item-title text-medium-h6 tutor-mt-30">
                        <span><?php $complete_count>($total_count/2) ? _e( 'You are almost done', 'tutor' ) : _e('Please complete profile') ?></span>:&nbsp;
                        <span class="color-text-primary">
                            <?php echo $complete_count . '/' . $total_count; ?>
                        </span>
                    </div>
                </div>
                <div class="tutor-bs-col-md-4 warning">
                    <ul>
                        <?php 
                            foreach ($profile_completion as $key => $data) {
                                $is_set = $data['is_set']; // Whether the step is done or not
                                ?>
                                <li>
                                    <?php if($is_set){ ?>
                                    <span class="icon ttr-tick-circle-outline-filled not-empty"></span>
                                    <?php } else { ?>
                                    <span class="ttr-cross-circle-outline-filled empty"></span>
                                    <?php } ?>
                                    <span>
                                        <?php echo $data['label_html']; ?>
                                    </span>
                                </li>
                                <?php
                            }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
}
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
                    <i class="ttr-book-open-filled"></i>
                </span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($enrolled_course_count); ?></span>
                <span><?php esc_html_e('Enrolled Courses', 'tutor'); ?></span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($enrolled_course_count); ?></span>
            </p>
        </div>
        <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
            <p>
                <span class="tutor-round-icon">
                    <i class="ttr-college-graduation-filled"></i>
                </span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($active_course_count); ?></span>
                <span><?php esc_html_e('Active Courses', 'tutor'); ?></span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($active_course_count); ?></span>
            </p>
        </div>
        <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
            <p>
                <span class="tutor-round-icon">
                    <i class="ttr-award-filled"></i>
                </span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($completed_course_count); ?></span>
                <span><?php esc_html_e('Completed Courses', 'tutor'); ?></span>
                <span class="tutor-dashboard-info-val"><?php echo esc_html($completed_course_count); ?></span>
            </p>
        </div>

		<?php
		if(current_user_can(tutor()->instructor_role)) :
			?>
            <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
                <p>
                    <span class="tutor-round-icon">
                        <i class="ttr-user-graduate-filled"></i>
                    </span>
                    <span class="tutor-dashboard-info-val"><?php echo esc_html($total_students); ?></span>
                    <span><?php esc_html_e('Total Students', 'tutor'); ?></span>
                    <span class="tutor-dashboard-info-val"><?php echo esc_html($total_students); ?></span>
                </p>
            </div>
            <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
                <p>
                    <span class="tutor-round-icon">
                        <i class="ttr-box-open-filled"></i>
                    </span>
                    <span class="tutor-dashboard-info-val"><?php echo esc_html(count($my_courses)); ?></span>
                    <span><?php esc_html_e('Total Courses', 'tutor'); ?></span>
                    <span class="tutor-dashboard-info-val"><?php echo esc_html(count($my_courses)); ?></span>
                </p>
            </div>
            <div class="tutor-bs-col-12 tutor-bs-col-sm-6 tutor-bs-col-md-6 tutor-bs-col-lg-4">
                <p>
                    <span class="tutor-round-icon">
                        <i class="ttr-coins-filled"></i>
                    </span>
                    <span class="tutor-dashboard-info-val"><?php echo tutor_utils()->tutor_price($earning_sum->instructor_amount); ?></span>
                    <span><?php esc_html_e('Total Earnings', 'tutor'); ?></span>
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
            <?php esc_html_e('Popular Courses', 'tutor'); ?>
            <a style="float:right" class="tutor-view-all-course" href="<?php echo esc_url(tutor_utils()->tutor_dashboard_url('my-courses')); ?>">
                <?php esc_html_e('View All', 'tutor'); ?>
            </a>
        </h3>
        <div class="tutor-dashboard-content-inner">
            <table class="tutor-ui-table tutor-ui-table-responsive table-popular-courses">
                <thead>
                    <tr>
                        <th>
                            <span class="text-regular-small color-text-subsued">
                                <?php esc_html_e('Course Name', 'tutor'); ?>
                            </span>
                        </th>
                        <th>
                            <div class="inline-flex-center color-text-subsued">
                                <span class="text-regular-small"><?php esc_html_e('Enrolled', 'tutor'); ?></span>
                                <span class="ttr-ordering-a-to-z-filled"></span>
                            </div>
                        </th>
                        <th>
                            <div class="inline-flex-center color-text-subsued">
                                <span class="text-regular-small"><?php esc_html_e('Rating', 'tutor'); ?></span>
                                <span class="ttr-ordering-a-to-z-filled"></span>
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
                            <td data-th="<?php esc_html_e('Course Name', 'tutor'); ?>" class="column-fullwidth">
                                <div class="td-course text-medium-body color-text-primary">
                                    <a href="<?php echo esc_url(get_the_permalink($course->ID)); ?>" target="_blank">
                                        <?php esc_html_e($course->post_title); ?>
                                    </a>
                                </div>
                            </td>
                            <td data-th="<?php esc_html_e('Enrolled', 'tutor'); ?>">
                                <span class="text-medium-caption color-text-primary">
                                    <?php esc_html_e($enrolled); ?>
                                </span>
                            </td>
                            <td data-th="<?php esc_html_e('Rating', 'tutor'); ?>">
                                <div class="td-tutor-rating text-regular-body color-text-subsued">
                                    <?php tutor_utils()->star_rating_generator($course_rating->rating_avg); ?> <span><?php esc_html_e($course_rating->rating_avg); ?></span>
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