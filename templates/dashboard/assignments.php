<?php
/**
 * Template for displaying Assignments
 *
 * @since v.1.3.4
 *
 * @author Themeum
 * @url https://themeum.com
 */

global $wpdb;



    $current_user = get_current_user_id();
    $assignments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts WHERE post_type = '{$wpdb->prefix}assignments' AND post_author = '{$current_user}' AND post_status = 'publish'");

    // @TODO: function should be moved to utils
    function get_course_id_by_assignment_id($assignment_id = 0){
        global $wpdb;
        $assignment_id = tutor_utils()->get_post_id($assignment_id);
        $topic_id = $wpdb->get_col("SELECT post_parent FROM {$wpdb->prefix}posts WHERE ID = $assignment_id")[0];
        $course_id = $wpdb->get_col("SELECT post_parent FROM {$wpdb->prefix}posts WHERE ID = $topic_id")[0];
        return $course_id;
    }
?>

<table>
    <thead>
        <tr>
            <th>Course Name</th>
            <th>Total Mark</th>
            <th>Total Submit</th>
            <th>#</th>
        </tr>
    </thead>
    <tbody>
        <?php
            foreach ($assignments as $item){
                $max_mark = tutor_utils()->get_assignment_option($item->ID, 'total_mark');
                $course_id = get_course_id_by_assignment_id($item->ID);
                if(get_post_status($course_id) !== 'publish') continue;
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
