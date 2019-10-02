<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $wpdb;

$assignment = sanitize_text_field($_GET['assignment']);
$assignments_submitted = $wpdb->get_results("SELECT * FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_post_ID = {$assignment}");

?>


<h3><?php esc_html_e('Assignment', 'tutor') ?></h3>
<div class="tutor-dashboard-info-table-wrap">
    <?php

    if (tutor_utils()->count($assignments_submitted)){

    ?>

    <table class="tutor-dashboard-info-table tutor-dashboard-assignment-submitted-table">
        <thead>
            <tr>
                <td><?php esc_attr_e('Student', 'tutor'); ?></td>
                <td><?php esc_attr_e('Date & Time', 'tutor'); ?></td>
                <td><?php esc_attr_e('Pass Mark', 'tutor'); ?></td>
                <td><?php esc_attr_e('Total Mark', 'tutor'); ?></td>
                <td><?php esc_attr_e('Result', 'tutor'); ?></td>
                <td>&nbsp;</td>
            </tr>
        </thead>

        <tbody>
        <?php

        foreach ($assignments_submitted as $assignment){
            $comment_author = get_user_by('login', $assignment->comment_author);
            $is_reviewed_by_instructor = get_comment_meta($assignment->comment_ID, 'evaluate_time', true);
            $max_mark = tutor_utils()->get_assignment_option($assignment->comment_post_ID, 'total_mark');
            $pass_mark = tutor_utils()->get_assignment_option($assignment->comment_post_ID, 'pass_mark');
            $given_mark = get_comment_meta($assignment->comment_ID, 'assignment_mark', true);
            $status = sprintf(__('%s Pending %s', 'tutor'), '<span class="pending">', '</span>');
            if(!empty($given_mark)){
                $status = (int) $given_mark >= (int) $pass_mark ? sprintf(__('%s Pass %s', 'tutor'), '<span class="pass">', '</span>') : sprintf(__('%s Fail %s', 'tutor'), '<span class="fail">', '</span>');
            }

            $review_url = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/review');

            ?>
            <tr>
                <td><?php echo $comment_author->display_name; ?></td>
                <td><?php echo date('j M, Y. h:i a', strtotime($assignment->comment_date)); ?></td>
                <td><?php echo $pass_mark; ?></td>
                <td><?php echo !empty($given_mark) ? $given_mark . '/' . $max_mark : $max_mark; ?></td>
                <td><?php echo $status; ?></td>
                <td> <?php echo "<a title='". __('Review this assignment', 'tutor') ."' href='".esc_url($review_url.'?view_assignment='.$assignment->comment_ID)."'><i class='tutor-icon-angle-right'></i> </a>"; ?> </td>
            </tr>
            <?php
        }

        ?>

        </tbody>
    </table>


    <?php

    }else{

        ?>

        <p><?php _e('No assignment has been submitted yet', 'tutor'); ?></p>
    <?php

    }
    ?>


</div>
