<?php
/**
 * Google class room settings section
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black">
		<?php echo esc_html( $section->label ); ?>
	</div>
	<button class="reset-btn reset_to_default" data-reset="<?php echo esc_attr( $section['slug'] ); ?>">
		<i class="btn-icon tutor-icon-refresh"></i>
		<?php esc_html_e( 'Reset to Default', 'tutor' ); ?>
	</button>
</div>
