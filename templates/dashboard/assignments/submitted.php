<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $wpdb;

$order_filter  = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'desc';
$assignment_id = sanitize_text_field($_GET['assignment']);
$assignments_submitted = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_post_ID = %d ORDER BY comment_ID $order_filter", $assignment_id));

?>

<div class="submitted-assignment-title">
    <a class="prev-btn" href="<?php echo tutor_utils()->get_tutor_dashboard_page_permalink('assignments'); ?>"><span>&leftarrow;</span><?php _e('Back to List', 'tutor'); ?></a>
    <h3><?php echo get_the_title($assignment_id); ?></h3>
</div>

<div class="tutor-announcement-table-wrap">
    <?php

    if (tutor_utils()->count($assignments_submitted)) {
    ?>

    <div class="tutor-dashboard-announcement-sorting-wrap submitted-assignments-sorting-wrap">
        <div class="tutor-form-group">
            <label><?php _e('Sort By:', 'tutor'); ?></label>
            <select class="tutor-announcement-order-sorting ignore-nice-select">
                <option value="desc" <?php selected($order_filter, 'desc'); ?>><?php _e('Latest', 'tutor'); ?></option>
                <option value="asc" <?php selected($order_filter, 'asc'); ?>><?php _e('Oldest', 'tutor'); ?></option>
            </select>
        </div>
    </div>

    <table class="tutor-dashboard-announcement-table tutor-dashboard-assignment-table" width="100%">
        <thead>
            <tr>
                <th style="width:25%;"><?php esc_attr_e('Date', 'tutor'); ?></td>
                <th><?php esc_attr_e('Student', 'tutor'); ?></td>
                <th style="width:15%;"><?php esc_attr_e('Total Marks', 'tutor'); ?></td>
                <th style="width:12%;"><?php esc_attr_e('Result', 'tutor'); ?></td>
                <th style="width:10%;">&nbsp;</td>
            </tr>
        </thead>

        <tbody>
        <?php

        foreach ($assignments_submitted as $assignment){
            $comment_author = get_user_by('login', $assignment->comment_author); // login=username
            /* echo "<pre>";
            print_r($comment_author); */
            $is_reviewed_by_instructor = get_comment_meta($assignment->comment_ID, 'evaluate_time', true);
            $max_mark = tutor_utils()->get_assignment_option($assignment->comment_post_ID, 'total_mark');
            $pass_mark = tutor_utils()->get_assignment_option($assignment->comment_post_ID, 'pass_mark');
            $given_mark = get_comment_meta($assignment->comment_ID, 'assignment_mark', true);
            $not_evaluated = $given_mark==='';
            $status = sprintf(__('%s Pending %s', 'tutor'), '<span class="review-required">', '</span>');
            $button_text = __('Evaluate', 'tutor');
            if(!empty($given_mark) || !$not_evaluated){
                $status = (int) $given_mark >= (int) $pass_mark ? sprintf(__('%s Pass %s', 'tutor'), '<span class="result-pass">', '</span>') : sprintf(__('%s Fail %s', 'tutor'), '<span class="result-fail">', '</span>');
                $button_text = __('Details', 'tutor');
            }

            $review_url = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/review');

            ?>
            <tr>
                <td><?php echo date('j M, Y,<\b\r>h:i a', strtotime($assignment->comment_date)); ?></td>
                <td>
                    <div class="student-column">
                        <div class="student-avatar">
                            <?php echo tutils()->get_tutor_avatar($comment_author->ID); ?>
                        </div>
                        <div class="student-details">
                            <h4><?php echo $comment_author->display_name; ?></h4>
                            <p><?php echo $comment_author->user_email; ?></p>
                        </div>
                    </div>
                </td>
                <td><?php echo !empty($given_mark) ? $given_mark . '/' . $max_mark : $max_mark; ?></td>
                <td><?php echo $status; ?></td>
                <td>
                    <a href="<?php echo esc_url($review_url.'?view_assignment='.$assignment->comment_ID).'&assignment='.$assignment_id; ?>" class="tutor-btn bordered-btn tutor-announcement-details">
                        <?php echo $button_text; ?>
                    </a>
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
