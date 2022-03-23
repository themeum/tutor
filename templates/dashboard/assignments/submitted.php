<?php

/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $wpdb;

$order_filter          = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'desc';
$assignment_id         = sanitize_text_field( $_GET['assignment'] );
$assignments_submitted = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_post_ID = %d ORDER BY comment_ID $order_filter", $assignment_id ) );

$max_mark  = tutor_utils()->get_assignment_option( $assignment_id, 'total_mark' );
$pass_mark = tutor_utils()->get_assignment_option( $assignment_id, 'pass_mark' );
$format    = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
$deadline  = tutor_utils()->get_assignment_deadline_date( $assignment_id, $format, __( 'No Limit', 'tutor' ) );
$comment_parent = !empty($assignments_submitted) ? $assignments_submitted[0]->comment_parent:null;
?>

<div class="tutor-dashboard-content-inner tutor-dashboard-assignment-submits">
	<div class="tutor-mb-24">
		<a class="tutor-back-btn tutor-color-design-dark" href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments' ) ); ?>">
			<span class="tutor-color-black assignment-back-icon tutor-icon-previous-line tutor-icon-30 tutor-mr-12"></span>
            <span class="tutor-color-black-60"><?php esc_html_e( 'Back', 'tutor' ); ?></span>
		</a>
	</div>

    <div class="tutor-assignment-review-header tutor-assignment-submitted-page">
        <div class="tutor-fs-7 tutor-color-black-60">
            <?php
            esc_html_e('Course', 'tutor'); ?> : <?php echo get_the_title($comment_parent); ?>
        </div>
        <div class="tutor-fs-6 tutor-fw-medium tutor-mt-8">
            <?php echo get_the_title($assignment_id); ?>
        </div>
        <div class="assignment-info tutor-mt-12 tutor-d-flex">
            <div class="tutor-fs-7 tutor-color-black-70">
                <?php esc_html_e('Submission Deadline', 'tutor'); ?>:
                <span class="tutor-fs-7 tutor-fw-medium"><?php echo $deadline; ?></span>
            </div>
            <div class="tutor-fs-7 tutor-color-black-70 tutor-ml-24">
                <?php esc_html_e('Total Points', 'tutor'); ?>:
                <span class="tutor-fs-7 tutor-fw-medium"><?php echo $max_mark; ?></span>
            </div>
            <div class="tutor-fs-7 tutor-color-black-70 tutor-ml-24">
                <?php esc_html_e('Pass Points', 'tutor'); ?>:
                <span class="tutor-fs-7 tutor-fw-medium"><?php echo $pass_mark; ?></span>
            </div>
        </div>
    </div>

    <div class="tutor-dashboard-announcement-sorting-wrap submitted-assignments-sorting-wrap">
        <div class="tutor-dashboard-announcement-sorting-input">
            <label class="tutor-fs-7 tutor-color-black-60"><?php esc_html_e( 'Sort By:', 'tutor' ); ?></label>
            <select class="tutor-announcement-order-sorting tutor-form-select tutor-form-control tutor-form-control-sm no-tutor-dropdown">
                <option value="desc" <?php selected( $order_filter, 'desc' ); ?>><?php esc_html_e( 'Latest', 'tutor' ); ?></option>
                <option value="asc" <?php selected( $order_filter, 'asc' ); ?>><?php esc_html_e( 'Oldest', 'tutor' ); ?></option>
            </select>
        </div>
    </div>

    <div class="tutor-ui-table-wrapper">
        <table class="tutor-ui-table tutor-ui-table-responsive">
            <thead>
                <tr>
                    <th>
                        <span class="tutor-fs-7 tutor-color-black-60">
                            <?php esc_html_e('Date', 'tutor'); ?>
                        </span>
                    </th>
                    <th>
                        <div class="inline-flex-center tutor-color-black-60">
                            <span class="tutor-fs-7">
                                <?php esc_html_e('Student', 'tutor'); ?>
                            </span>
                        </div>
                    </th>
                    <th>
                        <div class="inline-flex-center tutor-color-black-60">
                            <span class="tutor-fs-7">
                                <?php esc_html_e('Total Points', 'tutor'); ?>
                            </span>
                        </div>
                    </th>
                    <th>
                        <div class="inline-flex-center tutor-color-black-60">
                            <span class="tutor-fs-7">
                                <?php esc_html_e('Result', 'tutor'); ?>
                            </span>
                        </div>
                    </th>
                    <th class="tutor-shrink"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if (tutor_utils()->count($assignments_submitted)) {
                        foreach ($assignments_submitted as $assignment) {
                            $review_url                = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/review');
                            $comment_author            = get_user_by('login', $assignment->comment_author); // login=username
                            $is_reviewed_by_instructor = get_comment_meta($assignment->comment_ID, 'evaluate_time', true);
                            $given_mark                = get_comment_meta($assignment->comment_ID, 'assignment_mark', true);
                            $not_evaluated             = $given_mark === '';
                            $status                    = 'pending';
                            $button_text               = __('Evaluate', 'tutor');
    
                            if (!empty($given_mark) || !$not_evaluated) {
                                $status = (int)$given_mark >= (int)$pass_mark ? 'pass' : 'fail';
                                $button_text = __('Details', 'tutor');
                            }
                            ?>
                            <tr>
                                <td data-th="<?php esc_html_e('Date', 'tutor'); ?>">
                                    <span class="tutor-color-black tutor-fs-7 tutor-fw-medium">
                                        <?php echo wp_kses_post( date('j M, Y,<\b\r>h:i a', strtotime($assignment->comment_date))); ?>
                                    </span>
                                </td>
                                <td data-th="<?php esc_html_e('Student', 'tutor'); ?>">
                                    <div class="td-avatar">
                                        <img src="<?php echo get_avatar_url( $comment_author->ID ); ?>" alt=" - Profile Picture">
                                        <div class="tutor-fs-6 tutor-fw-medium  tutor-color-black">
                                            <?php esc_html_e( $comment_author->display_name ); ?><br/>
                                            <span class="tutor-fs-7">
                                                <?php esc_html_e( $comment_author->user_email ); ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td data-th="<?php esc_html_e('Total Points', 'tutor'); ?>">
                                    <span class="tutor-color-black tutor-fs-7 tutor-fw-medium">
                                        <?php echo !empty($given_mark) ? $given_mark . '/' . $max_mark : '&nbsp;'; ?>
                                    </span>
                                </td>
                                <td data-th="<?php esc_html_e('Result', 'tutor'); ?>">
                                    <?php echo tutor_utils()->translate_dynamic_text($status, true); ?>
                                </td>
                                <td data-th="<?php esc_html_e('Details URL', 'tutor'); ?>">
                                    <div class="inline-flex-center td-action-btns">
                                        <a href="<?php echo esc_url($review_url . '?view_assignment=' . $assignment->comment_ID) . '&assignment=' . $assignment_id; ?>" class="tutor-btn tutor-btn-disable-outline tutor-btn-outline-fd tutor-btn-sm">
                                            <?php esc_html_e($button_text); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="100%">
                                <div class="td-empty-state">
                                    <?php tutor_utils()->tutor_empty_state( 'No assignment', 'tutor' ); ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                ?>
            </tbody>
        </table>
    </div>

</div>
