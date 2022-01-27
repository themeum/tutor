<?php
/**
 * Template: Settings reset for each section.
 *
 * @package TutorLMS
 * @subpackage Settings
 * @since 2.0.0
 */

$section_label = isset( $section['label'] ) && ! empty( $section['label'] ) ? esc_attr( $section['label'] ) : '';
$section_slug  = isset( $section['slug'] ) && ! empty( $section['slug'] ) ? esc_attr( $section['slug'] ) : '';
// pr( $section );
?>
<div class="tutor-option-main-title">
	<h2><?php echo esc_attr( $section_label ); ?></h2>
	<button type="button" data-tutor-modal-target="tutor-modal-bulk-action"
			class="modal-reset-open"
			data-reset="<?php echo esc_attr( $section_slug ); ?>"
			data-heading="<?php echo esc_html( 'Reset to Default Settings?' ); ?>"
			data-message="<?php echo esc_html( 'WARNING! This will overwrite all customized settings of this section and reset them to default. Proceed with caution.' ); ?>" disabled>
		<i class="btn-icon tutor-icon-refresh-1-filled"></i>
		<?php echo esc_attr( 'Reset to Default' ); ?>
	</button>
</div>
