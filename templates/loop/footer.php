<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */
global $wp_query;
$is_enrolled_page = false;
$query_vars     = $wp_query->query_vars;
if ( isset( $query_vars[ 'tutor_dashboard_page' ] ) && 'enrolled-courses' === $query_vars['tutor_dashboard_page'] ) {
    $is_enrolled_page = true;
}
?>

<div class="tutor-course-listing-item-footer <?php echo esc_attr( $is_enrolled_page ? 'no-border' : 'has-border' ); ?> tutor-py-16 tutor-px-20">
    <?php tutor_course_loop_price(); ?>
</div>
