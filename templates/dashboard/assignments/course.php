<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $wpdb;
$course_id = (int) sanitize_text_field(tutor_utils()->array_get('course_id', $_GET));
$assignments = tutor_utils()->get_assignments_by_course($course_id);

if($assignments->count){
?>

<h3><?php echo get_the_title($course_id) ?></h3>
<div class="tutor-dashboard-info-table-wrap">
    <table class="tutor-dashboard-info-table tutor-dashboard-assignment-table">
        <thead>
        <tr>
            <th><?php _e('Course Name', 'tutor'); ?></th>
            <th><?php _e('Total Mark', 'tutor'); ?></th>
            <th><?php _e('Total Submit', 'tutor'); ?></th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($assignments->results as $item){
            $max_mark = tutor_utils()->get_assignment_option($item->ID, 'total_mark');
            $course_id = tutor_utils()->get_course_id_by_assignment($item->ID);
            $course_url = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/course');
            $submitted_url = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/submitted');
            $comment_count = $wpdb->get_var("SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_post_ID = $item->ID");
            // @TODO: assign post_meta is empty if user don't click on update button (http://prntscr.com/oax4t8) but post status is publish
            ?>
            <tr>
                <td>
                    <h5><?php echo $item->post_title ?></h5>
                    <h5><a href='<?php echo esc_url($course_url.'?course_id='.$course_id) ?>'><?php echo __('Course: ', 'tutor'). get_the_title($course_id); ?> </a></h5>
                </td>
                <td><?php echo $max_mark ?></td>
                <td><?php echo $comment_count ?></td>
                <td> <?php echo "<a title='". __('View Coures', 'tutor') ."' href='".esc_url($submitted_url.'?assignment='.$item->ID)."'><i class='tutor-icon-angle-right'></i> </a>"; ?> </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
<?php }else{
    echo '<p>'.__('No assignment available', 'tutor' ).'</p>';
}