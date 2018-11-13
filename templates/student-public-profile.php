<?php
/**
 * Template for displaying student Public Profile
 *
 * @since v.1.0.0
 *
 * @author Themeum
 * @url https://themeum.com
 */

get_header();

$user_name = get_query_var('tutor_student_username');
?>


<?php do_action('tutor_student/before/wrap'); ?>



    <div class="tutor-student-dashboard-leadinfo">
        <div class="tutor-container">

            <?php
                $current_uid = get_current_user_id();
                $user_data = get_userdata($current_uid);
            ?>

            <div class="tutor-row">
                <div class="tutor-col-auto">
                    <div class="tutor-dashboard-avater">
                        <?php
                        echo get_avatar($current_uid, '200'); ?>
                    </div>
                </div>
                <div class="tutor-col">
                    <div class="tutor-dashboard-student-info">
                        <h3 class="tutor-student-name">
                            <?php
                            echo esc_html($user_data->display_name);
                            ?>
                        </h3>
                        <ul class="tutor-dashboard-user-role">
                            <li>Admin</li>
                            <li>Student</li>
                        </ul>
                        <h4 class="tutor-designation">Founder & Ceo at Tutor</h4>
                        <h5 class="tutor-dashboard-user-location">
                            <!-- @TODO: Need Location Icon -->
                            <i class="icon icon-star-empty"></i>
                            Boston, MA, United Stats
                        </h5>
                    </div>
                    <div class="tutor-dashboard-student-meta">
                        <ul>
                            <li>
                                <h4>440</h4>
                                <span>Course Complete</span>
                            </li>
                            <li>
                                <h4>440</h4>
                                <span>Course Complete</span>
                            </li>
                            <li>
                                <h4>440</h4>
                                <span>Course Complete</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="tutor-seperator"></div>
        </div>
    </div>

<div <?php tutor_post_class('tutor-dashboard-student'); ?>>
    <div class="tutor-container">
        <div class="tutor-row">
            <div class="tutor-col-3">
                <ul class="tutor-dashboard-permalinks">
                    <li><a href="#">Bio</a></li>
                    <li><a href="#">Enrolled Course</a></li>
                    <li><a href="#">Review</a></li>
                </ul>
            </div>
            <div class="tutor-col-9">
                <div class="tutor-dashboard-content">
                    <h3>Student Profile</h3>
                </div>
            </div>
        </div> <!-- .tutor-row -->
    </div> <!-- .tutor-container -->
</div>

<?php do_action('tutor_student/after/wrap'); ?>

<?php
get_footer();
