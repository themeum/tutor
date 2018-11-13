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


<div <?php tutor_post_class('tutor-full-width-student-profile'); ?>>
    <div class="tutor-container">
        <div class="tutor-row">

            <div class="tutor-col-3">


            </div> <!-- .tutor-col-4 -->

            <div class="tutor-col-9">

                <h3>Student Profile</h3>

            </div> <!-- .tutor-col-8 -->


        </div> <!-- .tutor-row -->
    </div> <!-- .tutor-container -->
</div>

<?php do_action('tutor_student/after/wrap'); ?>

<?php
get_footer();
