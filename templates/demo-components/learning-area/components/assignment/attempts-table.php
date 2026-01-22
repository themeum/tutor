<?php
/**
 * Attempts Table
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

$render_status = function ( $status ) {
	$variant = '';

	if ( 'passed' === $status ) {
		$variant = 'success';
	} elseif ( 'failed' === $status ) {
		$variant = 'error';
	} elseif ( 'pending' === $status ) {
		$variant = 'warning';
	}

	return '<div class="tutor-badge tutor-capitalize tutor-badge-rounded tutor-badge-' . esc_attr( $variant ) . '">' . esc_html( $status ) . '</div>';
};

// @TODO: Will be removed later
$attempt_details_url = function ( $attempt ) {
	// @TODO: Will be removed later
	$attemps_url = add_query_arg(
		array(
			'subpage'    => 'assignment',
			'attempt_id' => $attempt['attempt_id'],
		),
		remove_query_arg( 'attempts' )
	);

	return $attemps_url;
};
?>

<div class="tutor-table-wrapper tutor-sm-hidden">
	<table class="tutor-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Attempt Date', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Total Marks', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Pass Marks', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Earned Marks', 'tutor' ); ?></th>
				<th><?php esc_html_e( 'Result', 'tutor' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $attempts as $attempt ) : ?>
				<tr>
					<td>
						<a href="<?php echo esc_url( $attempt_details_url( $attempt ) ); ?>">
							<?php echo esc_html( date_i18n( 'D, ' . get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $attempt['attempt_date'] ) ) ); ?>
						</a>
					</td>
					<td><?php echo esc_html( $attempt['total_marks'] ); ?></td>
					<td><?php echo esc_html( $attempt['pass_marks'] ); ?></td>
					<td><?php echo esc_html( $attempt['earned_marks'] ? $attempt['earned_marks'] : '-' ); ?></td>
					<td><?php echo wp_kses_post( $render_status( $attempt['status'] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<!-- Below sm breakpoint, show table as a list -->
<div class="tutor-assignment-attempts-list tutor-hidden tutor-sm-block">
	<?php foreach ( $attempts as $attempt ) : ?>
		<div class="tutor-assignment-attempts-list-item">
			<div class="tutor-flex tutor-items-center tutor-justify-between">
				<a href="<?php echo esc_url( $attempt_details_url( $attempt ) ); ?>" class="tutor-small tutor-text-brand tutor-font-medium">
					<?php echo esc_html( date_i18n( 'D, ' . get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $attempt['attempt_date'] ) ) ); ?>
				</a>

				<?php echo wp_kses_post( $render_status( $attempt['status'] ) ); ?>
			</div>

			<div class="tutor-assignment-attempts-list-card">
				<div class="tutor-flex tutor-flex-column tutor-gap-3">
					<span class="tutor-small tutor-text-subdued">
						<?php esc_html_e( 'Total Marks', 'tutor' ); ?>
					</span>
					<span class="tutor-p1 tutor-font-medium tutor-text-secondary">
						<?php echo esc_html( $attempt['total_marks'] ); ?>
					</span>
				</div>

				<div class="tutor-flex tutor-flex-column tutor-gap-3">
					<span class="tutor-small tutor-text-subdued">
						<?php esc_html_e( 'Pass Marks', 'tutor' ); ?>
					</span>
					<span class="tutor-p1 tutor-font-medium tutor-text-secondary">
						<?php echo esc_html( $attempt['pass_marks'] ); ?>
					</span>
				</div>

				<div class="tutor-flex tutor-flex-column tutor-gap-3">
					<span class="tutor-small tutor-text-subdued">
						<?php esc_html_e( 'Earned Marks', 'tutor' ); ?>
					</span>
					<span class="tutor-p1 tutor-font-medium tutor-text-secondary">
						<?php echo esc_html( $attempt['earned_marks'] ? $attempt['earned_marks'] : '-' ); ?>
					</span>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>