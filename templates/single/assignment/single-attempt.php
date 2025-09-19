<?php
/**
 * Single attempt page
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.8.2
 */

use Tutor\Helpers\DateTimeHelper;
use TUTOR_ASSIGNMENTS\Assignments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attempt_id           = $data['attempt_id'];
$remaining_time       = $data['remaining_time'];
$now                  = $data['now'];
$time_value           = $data['time_value'];
$post_id              = get_the_ID(); //phpcs:ignore
$user_id              = get_current_user_id();
$user_data            = get_userdata( $user_id );
$assignment_comment   = tutor_utils()->get_single_comment_user_post_id( $post_id, $user_id );
$submitted_assignment = tutor_utils()->is_assignment_submitted( get_the_ID(), 0, $attempt_id );
if ( ! $submitted_assignment ) {
	return;
}
?>
<?php
	$is_reviewed_by_instructor = get_comment_meta( $submitted_assignment->comment_ID, 'evaluate_time', true );

	$assignment_id = $submitted_assignment->comment_post_ID;
	$submit_id     = $submitted_assignment->comment_ID;

	$total_mark = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'total_mark' );
	$pass_mark  = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'pass_mark' );
	$given_mark = get_comment_meta( $submitted_assignment->comment_ID, 'assignment_mark', true );
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
		</tr>
		</thead>

		<tbody>
		<tr>
			<td>
				<?php echo esc_html( DateTimeHelper::get_gmt_to_user_timezone_date( $submitted_assignment->comment_date_gmt ) ); ?>
			</td>
			<td><?php esc_html_e( $total_mark, 'tutor' );//phpcs:ignore ?></td>
			<td><?php esc_html_e( $pass_mark, 'tutor' );//phpcs:ignore ?></td>
			<td><?php esc_html_e( $given_mark, 'tutor' );//phpcs:ignore ?></td>
			<td>
				<?php if ( $is_reviewed_by_instructor ) : ?>
					<?php if ( $given_mark >= $pass_mark ) : ?>
						<span class="tutor-badge-label label-success">
							<?php esc_html_e( 'Passed', 'tutor' ); ?>
						</span>
					<?php else : ?>
						<span class="tutor-badge-label label-danger">
							<?php esc_html_e( 'Failed', 'tutor' ); ?>
						</span>
					<?php endif; ?>
				<?php endif; ?>
		
				<?php if ( ! $is_reviewed_by_instructor ) : ?>
					<span class="tutor-badge-label label-warning">
						<?php esc_html_e( 'Pending', 'tutor' ); ?>
					</span>
				<?php endif; ?>
			</td>
		</tr>
		</tbody>
	</table>
	</div>
</div>

<?php
	$instructor_note = get_comment_meta( $submitted_assignment->comment_ID, 'instructor_note', true );
if ( ! empty( $instructor_note ) && $is_reviewed_by_instructor ) :
	?>
	<div class="tutor-instructor-note tutor-my-32 tutor-py-20 tutor-px-24 tutor-py-sm-32 tutor-px-sm-36">
	<div class="tutor-in-title tutor-fs-6 tutor-fw-medium tutor-color-black">
	<?php esc_html_e( 'Instructor Note', 'tutor' ); ?>
	</div>
	<div class="tutor-in-body tutor-fs-6 tutor-color-secondary tutor-pt-12 tutor-pt-sm-16">
	<?php echo wp_kses_post( nl2br( get_comment_meta( $submitted_assignment->comment_ID, 'instructor_note', true ) ) ); ?>
	</div>
</div>
<?php endif; ?>

<div class="tutor-assignment-details  tutor-pb-48 tutor-pb-sm-72">
	<div class="tutor-ar-body tutor-pt-24 tutor-pb-40 tutor-px-16 tutor-px-md-32">
		<div class="tutor-ar-header tutor-d-flex tutor-justify-between tutor-align-center">
			<div class="tutor-ar-title tutor-fs-6 tutor-fw-medium tutor-color-black">
				<?php esc_html_e( 'Your Assignment', 'tutor' ); ?>
			</div>

			<?php
			$result = Assignments::get_assignment_result( $post_id, $user_id );
			if ( in_array( $result, array( 'pending', 'fail' ), true ) && ( $remaining_time > $now || 0 === $time_value ) ) :
				?>
				<div class="tutor-ar-btn">
					<a href="<?php echo esc_url( add_query_arg( 'update-assignment', $submitted_assignment->comment_ID ) ); ?>"
						class="tutor-btn tutor-btn-outline-primary tutor-btn-sm">
					<?php esc_html_e( 'Edit', 'tutor' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<div class="tutor-fs-6 tutor-color-secondary tutor-pt-16 tutor-entry-content">
			<?php echo wp_kses_post( nl2br( stripslashes( $submitted_assignment->comment_content ) ) ); ?>
		</div>

		<?php
			$attached_files = get_comment_meta( $submitted_assignment->comment_ID, 'uploaded_attachments', true );
		if ( $attached_files ) :
			?>
			<?php
			$attached_files = json_decode( $attached_files, true );
			if ( tutor_utils()->count( $attached_files ) ) :
				?>
					<div class="tutor-attachment-files submited-files tutor-d-flex tutor-flex-column tutor-mt-20 tutor-mt-sm-40">
						<?php foreach ( $attached_files as $attached_file ) : ?>
							<div class="tutor-instructor-card tutor-mt-12">
								<div class="tutor-icard-content">
									<div class="tutor-fs-6 tutor-color-secondary">
								<?php echo esc_html( tutor_utils()->array_get( 'name', $attached_file ) ); ?>
									</div>
									<div class="tutor-fs-7"><?php esc_html_e( 'Size', 'tutor' ); ?>:
								<?php
									echo esc_html(
										tutor_utils()->get_readable_filesize( $upload_basedir . $attached_file['uploaded_path'] )
									);
								?>
									</div>
								</div>
								<div class="tutor-d-flex tutor-align-center">
									<a class="tutor-iconic-btn tutor-iconic-btn-outline" download
										href="<?php echo esc_url( $upload_baseurl . tutor_utils()->array_get( 'uploaded_path', $attached_file ) ); ?>"
										target="_blank">
										<span class="tutor-icon-download"></span>
									</a>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
	</div>
</div>
	