<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'TUTOR_PRO_VERSION' ) ) {
    return;
}
use TUTOR_ASSIGNMENTS\Assignments_List;

$order_filter          = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'desc';
$assignment_id         = sanitize_text_field( $_GET['assignment'] );
$assignments_submitted = Assignments_List::get_submitted_assignments( $assignment_id, $order_filter );

$max_mark  = tutor_utils()->get_assignment_option( $assignment_id, 'total_mark' );
$pass_mark = tutor_utils()->get_assignment_option( $assignment_id, 'pass_mark' );
$format    = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
$deadline  = tutor_utils()->get_assignment_deadline_date( $assignment_id, $format, __( 'No Limit', 'tutor' ) );
$comment_parent = !empty($assignments_submitted) ? $assignments_submitted[0]->comment_parent:null;
?>

<div class="tutor-dashboard-content-inner tutor-dashboard-assignment-submits">
	<div class="tutor-mb-24">
		<a class="tutor-btn tutor-btn-ghost" href="<?php echo esc_url( tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments' ) ); ?>">
			<span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span>
            <?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
	</div>

    <div class="tutor-assignment-review-header tutor-assignment-submitted-page">
        <div class="tutor-fs-7 tutor-color-secondary">
            <?php esc_html_e('Course', 'tutor'); ?> : <?php echo get_the_title($comment_parent); ?>
        </div>
        <div class="tutor-fs-6 tutor-fw-medium tutor-mt-8">
            <?php echo get_the_title($assignment_id); ?>
        </div>
        <div class="assignment-info tutor-mt-12 tutor-d-flex">
            <div class="tutor-fs-7 tutor-color-secondary">
                <?php esc_html_e('Submission Deadline', 'tutor'); ?>:
                <span class="tutor-fs-7 tutor-fw-medium"><?php echo $deadline; ?></span>
            </div>
            <div class="tutor-fs-7 tutor-color-secondary tutor-ml-24">
                <?php esc_html_e('Total Points', 'tutor'); ?>:
                <span class="tutor-fs-7 tutor-fw-medium"><?php echo $max_mark; ?></span>
            </div>
            <div class="tutor-fs-7 tutor-color-secondary tutor-ml-24">
                <?php esc_html_e('Pass Points', 'tutor'); ?>:
                <span class="tutor-fs-7 tutor-fw-medium"><?php echo $pass_mark; ?></span>
            </div>
        </div>
    </div>

    <div class="tutor-dashboard-announcement-sorting-wrap submitted-assignments-sorting-wrap">
        <div class="tutor-dashboard-announcement-sorting-input">
            <label class="tutor-fs-7 tutor-color-secondary"><?php esc_html_e( 'Sort By:', 'tutor' ); ?></label>
            <select class="tutor-announcement-order-sorting tutor-form-control">
                <option value="desc" <?php selected( $order_filter, 'desc' ); ?>><?php esc_html_e( 'Latest', 'tutor' ); ?></option>
                <option value="asc" <?php selected( $order_filter, 'asc' ); ?>><?php esc_html_e( 'Oldest', 'tutor' ); ?></option>
            </select>
        </div>
    </div>

    <?php if (tutor_utils()->count($assignments_submitted)) : ?>
        <div class="tutor-table-responsive">
            <table class="tutor-table tutor-table-middle">
                <thead>
                    <tr>
                        <th width="20%">
                            <?php esc_html_e('Date', 'tutor'); ?>
                        </th>
                        <th width="30%">
                            <?php esc_html_e('Student', 'tutor'); ?>
                        </th>
                        <th>
                            <?php esc_html_e('Total Points', 'tutor'); ?>
                        </th>
                        <th>
                            <?php esc_html_e('Result', 'tutor'); ?>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments_submitted as $assignment) :
                        $review_url                = tutor_utils()->get_tutor_dashboard_page_permalink('assignments/review');
                        $comment_author            = get_user_by('login', $assignment->comment_author); // login=username
                        $is_reviewed_by_instructor = get_comment_meta($assignment->comment_ID, 'evaluate_time', true);
                        $given_mark                = get_comment_meta($assignment->comment_ID, 'assignment_mark', true);
                        $not_evaluated             = $given_mark === '';
                        $status                    = 'pending';
                        $button_text               = __('Evaluate', 'tutor');

                        if ( !empty( $given_mark ) || !$not_evaluated ) {
                            $status         = (int)$given_mark >= (int)$pass_mark ? 'pass' : 'fail';
                            $button_text    = __('Details', 'tutor');
                        }
                    ?>
                        <tr>
                            <td>
                                <?php echo wp_kses_post( date('j M, Y,<\b\r>h:i a', strtotime($assignment->comment_date))); ?>
                            </td>

                            <td>
                                <div class="tutor-d-flex tutor-align-center tutor-gap-2">
                                    <?php echo tutor_utils()->get_tutor_avatar( $comment_author->ID ); ?>
                                    <div>
                                        <?php esc_html_e( $comment_author->display_name ); ?><br/>
                                        <span class="tutor-fs-7 tutor-fw-normal tutor-color-muted">
                                            <?php esc_html_e( $comment_author->user_email ); ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="tutor-color-black tutor-fs-7 tutor-fw-medium">
                                    <?php echo !empty($given_mark) ? $given_mark . '/' . $max_mark : '&nbsp;'; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo tutor_utils()->translate_dynamic_text($status, true); ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url($review_url . '?view_assignment=' . $assignment->comment_ID) . '&assignment=' . $assignment_id; ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
                                    <?php esc_html_e($button_text); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <?php tutor_utils()->tutor_empty_state( 'No assignment', 'tutor' ); ?>
    <?php endif; ?>
</div>
