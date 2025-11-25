<?php
/**
 * Tutor Assignment.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

/**
 * Check if the current page is the subscription details page.
 *
 * @return bool
 * @since 4.0.0
 */
function is_assignment_edit_page() {
	// Using filter_input which doesn't add slashes.
	$subpage         = filter_input( INPUT_GET, 'subpage', FILTER_SANITIZE_SPECIAL_CHARS );
	$edit             = filter_input( INPUT_GET, 'edit', FILTER_SANITIZE_SPECIAL_CHARS );

	return 'assignment' === $subpage &&
			'true' === $edit;
}

?>

<div class="tutor-assignment">
	<?php if ( ! is_assignment_edit_page() ) : ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.assignment.overview' ); ?>
	<?php else : ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.assignment.edit' ); ?>
	<?php endif; ?>
</div>