<?php
/**
 * Multiple attempts page
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.2
 */

use Tutor\Helpers\DateTimeHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attempts = $data['attempts'] ?? array();
if ( empty( $attempts ) ) {
	return;
}
?>
<div class="tutor-assignment-result-table tutor-mt-32 tutor-mb-40">
	<div class="tutor-table-responsive">
		<table class="tutor-table my-quiz-attempts">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'tutor' ); ?></th>
					<th><?php esc_html_e( 'Total Marks', 'tutor' ); ?></th>
					<th><?php esc_html_e( 'Pass Marks', 'tutor' ); ?></th>
					<th><?php esc_html_e( 'Earned Marks', 'tutor' ); ?></th>
					<th><?php esc_html_e( 'Result', 'tutor' ); ?></th>
					<th><?php esc_html_e( 'Details', 'tutor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $attempts as $submitted_assignment ) :
					$is_reviewed_by_instructor = get_comment_meta( $submitted_assignment->comment_ID, 'evaluate_time', true );
					$assignment_id             = $submitted_assignment->comment_post_ID;
					$submit_id                 = $submitted_assignment->comment_ID;

					$total_mark  = tutor_utils()->get_assignment_option( $assignment_id, 'total_mark' );
					$pass_mark   = tutor_utils()->get_assignment_option( $assignment_id, 'pass_mark' );
					$given_mark  = get_comment_meta( $submit_id, 'assignment_mark', true );
					$details_url = add_query_arg(
						array(
							'view_assignment_attempt_id' => $submit_id,
						),
						get_permalink( $assignment_id )
					);
					?>
					<tr>
						<td><?php echo esc_html( DateTimeHelper::get_gmt_to_user_timezone_date( $submitted_assignment->comment_date_gmt ) ); ?></td>
                        <td><?php esc_html_e( $total_mark, 'tutor' ); // phpcs:ignore ?></td>
                        <td><?php esc_html_e( $pass_mark, 'tutor' ); // phpcs:ignore ?></td>
                        <td><?php esc_html_e( $given_mark, 'tutor' ); // phpcs:ignore ?></td>
						<td>
							<?php if ( $is_reviewed_by_instructor ) : ?>
								<?php if ( $given_mark >= $pass_mark ) : ?>
									<span class="tutor-badge-label label-success"><?php esc_html_e( 'Passed', 'tutor' ); ?></span>
								<?php else : ?>
									<span class="tutor-badge-label label-danger"><?php esc_html_e( 'Failed', 'tutor' ); ?></span>
								<?php endif; ?>
							<?php else : ?>
								<span class="tutor-badge-label label-warning"><?php esc_html_e( 'Pending', 'tutor' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<a href="<?php echo esc_url( $details_url ); ?>" class="tutor-btn tutor-btn-tertiary tutor-btn-sm">
								<?php esc_html_e( 'Details', 'tutor' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>