<?php
/**
 * Assignment Edit
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$back_url = add_query_arg(
	array(
		'subpage' => 'assignment',
	),
	remove_query_arg( 'edit' )
);

$assignment_title = 'React Fundamentals: Building Your First Component';

?>

<div class="tutor-assignment-edit">
	<div>
		<a href="<?php echo esc_url( $back_url ); ?>" class="tutor-btn tutor-btn-secondary tutor-gap-2">
			<?php tutor_utils()->render_svg_icon( Icon::ARROW_LEFT ); ?>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
	</div>

	<div class="tutor-assignment-form">
		<div class="tutor-small tutor-text-brand">
			<?php echo esc_html( $assignment_title ); ?>
		</div>

		<h4 class="tutor-h4">
			<?php esc_html_e( 'Submit Assignment', 'tutor' ); ?>
		</h4>
	</div>
</div>