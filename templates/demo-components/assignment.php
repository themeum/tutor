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
 * Check if the current page is the edit page.
 *
 * @return bool
 * @since 4.0.0
 */
function is_assignment_edit_page() {
	// Using filter_input which doesn't add slashes.
	$subpage = filter_input( INPUT_GET, 'subpage', FILTER_SANITIZE_SPECIAL_CHARS );
	$edit    = filter_input( INPUT_GET, 'edit', FILTER_SANITIZE_SPECIAL_CHARS );

	return 'assignment' === $subpage &&
			'true' === $edit;
}

/**
 * Check if the current page is the attempts page.
 *
 * @return bool
 * @since 4.0.0
 */
function is_assignment_attempts_page() {
	// Using filter_input which doesn't add slashes.
	$subpage  = filter_input( INPUT_GET, 'subpage', FILTER_SANITIZE_SPECIAL_CHARS );
	$attempts = filter_input( INPUT_GET, 'attempts', FILTER_SANITIZE_SPECIAL_CHARS );

	return 'assignment' === $subpage &&
			'true' === $attempts;
}

/**
 * Check if the current page is the attempts page.
 *
 * @return bool
 * @since 4.0.0
 */
function is_assignment_attempt_details_page() {
	// Using filter_input which doesn't add slashes.
	$subpage  = filter_input( INPUT_GET, 'subpage', FILTER_SANITIZE_SPECIAL_CHARS );
	$attempt_id = filter_input( INPUT_GET, 'attempt_id', FILTER_SANITIZE_SPECIAL_CHARS );

	return 'assignment' === $subpage &&
			! empty( $attempt_id );
}

?>

<div class="tutor-assignment">
	<?php if ( is_assignment_edit_page() ) : ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.assignment.edit' ); ?>
	<?php elseif ( is_assignment_attempts_page() ) : ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.assignment.attempts' ); ?>
	<?php elseif ( is_assignment_attempt_details_page() ) : ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.assignment.attempt-details' ); ?>
	<?php else : ?>
		<?php tutor_load_template( 'demo-components.learning-area.components.assignment.overview' ); ?>
	<?php endif; ?>
</div>