<?php
/**
 * Template: Settings reset for each section.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$section_label = isset( $section['label'] ) && ! empty( $section['label'] ) ? esc_attr( $section['label'] ) : '';
$section_slug  = isset( $section['slug'] ) && ! empty( $section['slug'] ) ? esc_attr( $section['slug'] ) : '';
?>
<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black" tutor-option-title><?php echo esc_attr( $section_label ); ?></div>
	<button type="button" data-tutor-modal-target="tutor-modal-bulk-action"
			class="tutor-btn tutor-btn-ghost modal-reset-open"
			data-reset="<?php echo esc_attr( $section_slug ); ?>"
			data-heading="<?php esc_html_e( 'Reset to Default Settings?', 'tutor' ); ?>"
			data-message="<?php esc_html_e( 'WARNING! This will overwrite all customized settings of this section and reset them to default. Proceed with caution.', 'tutor' ); ?>" disabled>
			<i class="btn-icon tutor-icon-refresh tutor-mr-8" area-hidden="true"></i>
			<?php esc_html_e( 'Reset to Default', 'tutor' ); ?>
	</button>
</div>
