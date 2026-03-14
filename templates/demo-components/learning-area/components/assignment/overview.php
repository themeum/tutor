<?php
/**
 * Assignment Overview
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$assignment_status = 'Not Started';
$assignment_title  = 'React Fundamentals: Building Your First Component';
$total_marks       = '100';
$passing_marks     = '50';
$duration          = '2 weeks';
$deadline          = '3 Days, 23 Hours';

// @TODO: Need to use proper status
$render_status = function ( $status ) {
	$variant = 'secondary';

	if ( 'not-started' === $status ) {
		$variant = '';
	} elseif ( 'in-progress' === $status ) {
		$variant = 'warning';
	} elseif ( 'completed' === $status ) {
		$variant = 'success';
	}

	return '<div class="tutor-badge tutor-badge-' . esc_attr( $variant ) . '">' . esc_html( $status ) . '</div>';
};

?>


<div class="tutor-assignment-overview">
	<div class="tutor-assignment-info">
		<?php echo wp_kses_post( $render_status( $assignment_status ) ); ?>

		<h4 class="tutor-h4 tutor-sm-text-medium tutor-mt-1">
			<?php echo esc_html( $assignment_title ); ?>
		</h4>

		<div class="tutor-table-wrapper tutor-table-column-borders">
			<table class="tutor-table">
				<tr class="tutor-assignment-info-column">
					<td>
						<div class="tutor-flex tutor-items-center tutor-gap-4">
							<?php tutor_utils()->render_svg_icon( Icon::COMPLETED_CIRCLE, 20, 20 ); ?>
							<span class="tutor-small tutor-text-secondary">
								<?php esc_html_e( 'Total Marks', 'tutor' ); ?>
							</span>
						</div>
					</td>
					<td>
						<div class="tutor-flex tutor-items-center">
							<span class="tutor-small tutor-font-medium tutor-text-secondary">
								<?php echo esc_html( $total_marks ); ?>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="tutor-flex tutor-items-center tutor-gap-4">
							<?php tutor_utils()->render_svg_icon( Icon::PASSED, 20, 20 ); ?>
							<span class="tutor-small tutor-text-secondary">
								<?php esc_html_e( 'Passing Marks', 'tutor' ); ?>
							</span>
						</div>
					</td>
					<td>
						<div class="tutor-flex tutor-items-center">
							<span class="tutor-small tutor-font-medium tutor-text-secondary">
								<?php echo esc_html( $passing_marks ); ?>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="tutor-flex tutor-items-center tutor-gap-4">
							<?php tutor_utils()->render_svg_icon( Icon::CLOCK, 20, 20 ); ?>
							<span class="tutor-small tutor-text-secondary">
								<?php esc_html_e( 'Duration', 'tutor' ); ?>
							</span>
						</div>
					</td>
					<td>
						<div class="tutor-flex tutor-items-center">
							<span class="tutor-small tutor-font-medium tutor-text-secondary">
								<?php echo esc_html( $duration ); ?>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="tutor-flex tutor-items-center tutor-gap-4">
							<?php tutor_utils()->render_svg_icon( Icon::CALENDAR_2, 20, 20 ); ?>
							<span class="tutor-small tutor-text-secondary">
								<?php esc_html_e( 'Deadline', 'tutor' ); ?>
							</span>
						</div>
					</td>
					<td>
						<div class="tutor-flex tutor-items-center">
							<span class="tutor-small tutor-font-medium tutor-text-secondary">
								<?php echo esc_html( $deadline ); ?>
							</span>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<?php tutor_load_template( 'demo-components.learning-area.components.assignment.details' ); ?>

	<div class="tutor-assignment-actions">
		<button class="tutor-btn tutor-btn-ghost tutor-btn-medium">
			<?php esc_html_e( 'Skip Assignment', 'tutor' ); ?>
		</button>
		<a href="<?php echo esc_url( add_query_arg( 'edit', 'true' ) ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-medium">
			<?php esc_html_e( 'Start Assignment', 'tutor' ); ?>
		</a>
	</div>
</div>