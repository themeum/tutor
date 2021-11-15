<div class="tutor-option-main-title">
	<h2><?php echo esc_attr( $section_label ); ?></h2>
	<button data-tutor-modal-target="tutor-modal-bulk-action"
			class="modal-reset-open"
			data-reset="<?php echo esc_attr( $section_slug ); ?>"
			data-heading="<?php echo esc_html( 'Reset to Default Settings?' ); ?>"
			data-message="<?php echo esc_html( 'WARNING! This will overwrite all customized settings of this section and reset them to default. Proceed with caution.' ); ?>">
		<i class="btn-icon ttr-refresh-1-filled"></i>
		<?php echo esc_attr( 'Reset to Default' ); ?>
	</button>
</div>
