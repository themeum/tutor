<?php
/**
 * Template: Modal for confirmation
 *
 * @package TutorLMS
 * @subpackage Settings
 * @since 2.0.0
 */

?>
<div id="tutor-modal-bulk-action" class="tutor-modal tutor-modal-confirmation">
	<span class="tutor-modal-overlay"></span>
	<button data-tutor-modal-close class="tutor-modal-close">
		<span class="tutor-icon-times"></span>
	</button>
	<div class="tutor-modal-window">
		<div class="tutor-modal-content">
			<div class="tutor-modal-body tutor-text-center">
				<div class="tutor-modal-icon">
					<img src="<?php echo esc_url( tutor()->icon_dir . 'reset.svg' ); ?>" alt="reset-icon"/>
				</div>
				<div class="tutor-modal-text-wrap">
					<h3 class="tutor-modal-title">Null</h3>
				</div>
				<div class="tutor-alert tutor-warning tutor-mt-32">
					<div class="tutor-alert-text">
						<span class="tutor-alert-icon tutor-fs-4 tutor-icon-warning tutor-mr-12"></span>
						<span class="color-warning-100 tutor-modal-message">Null</span>
					</div>
				</div>
				<div class="tutor-modal-btns tutor-btn-group tutor-mt-40">
					<button data-tutor-modal-close class="tutor-btn tutor-btn-outline-primary">
					<?php echo esc_attr( 'Cancel' ); ?>
					</button>
					<button class="tutor-btn tutor-btn-primary reset_to_default" data-reset-for="Null" data-reset="Null">
						<?php echo esc_attr( 'Reset' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
