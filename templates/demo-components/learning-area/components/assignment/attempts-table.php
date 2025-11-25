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
	$variant = 'secondary';

	if ( 'passed' === $status ) {
		$variant = 'completed';
	} elseif ( 'failed' === $status ) {
		$variant = 'cancelled';
	} elseif ( 'pending' === $status ) {
		$variant = 'pending';
	}

	return '<div class="tutor-badge tutor-capitalize tutor-badge-small tutor-badge-circle tutor-badge-' . esc_attr( $variant ) . '">' . esc_html( $status ) . '</div>';
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

<div class="tutor-table-wrapper">
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