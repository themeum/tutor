<?php
/**
 * Import export tools view
 *
 * @package Tutor\Views
 * @subpackage Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

?>
<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black">
		<?php esc_html_e( 'Import/Export', 'tutor' ); ?>
	</div>
</div>

<?php
tutor_alert(
	__( 'Warning: Importing, Restoring, or Resetting will overwrite ALL existing settings. Please proceed with caution.', 'tutor' ),
	'warning'
);
?>

<div class="tutor-option-single-item tutor-mb-32">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Export', 'tutor' ); ?></div>
	</div>

	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<div class="label tutor-fs-6 tutor-color-black"><?php esc_html_e( 'Current Settings', 'tutor' ); ?></div>
				<div class="desc tutor-fs-8 tutor-fw-medium tutor-color-secondary">
					<span style="font-weight: 500"><?php esc_html_e( 'Last Update', 'tutor' ); ?>: </span> <?php echo esc_html( get_option( 'tutor_option_update_time' ) ); ?>
				</div>
			</div>
			<div class="tutor-option-field-input">
				<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" id="export_settings"><?php esc_html_e( 'Export Settings', 'tutor' ); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32 item-variation-dragndrop import-setting">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Import Settings', 'tutor' ); ?></div>
	</div>
	<!-- @todo: fix the upload button -->
	<div class="item-wrapper">
		<div class="tutor-option-field-row tutor-d-block">
			<div class="tutor-option-field-label">
				<div class="drag-drop-zone">
					<span class="tutor-icon-upload tutor-fs-1 tutor-color-primary"></span>
					<div class="title"><?php esc_html_e( 'Drag &amp; Drop your JSON File here', 'tutor' ); ?> </div>
					<div class="subtitle"><span><?php esc_html_e( 'File Format', 'tutor' ); ?>:</span> .json <br> <?php esc_html_e( 'Or', 'tutor' ); ?></div>
					<label for="drag-drop-input" class="tutor-btn tutor-btn-primary tutor-btn-sm">
						<input type="file" name="drag-drop-input" id="drag-drop-input" class="tutor-d-none">
						<span><?php esc_html_e( 'Browse File', 'tutor' ); ?></span>
					</label>
					<span class="file-info"></span>
				</div>
			</div>
			<div class="tutor-option-field-input tutor-mt-16">
				<button class="tutor-btn tutor-btn-primary tutor-btn-md tutor_import_options" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="<?php esc_attr_e( 'Yes, Import Settings', 'tutor' ); ?>" data-heading="<?php esc_attr_e( 'Import from Previous Settings?', 'tutor' ); ?>" data-message="<?php esc_attr_e( 'WARNING! This will overwrite all existing settings, please proceed with caution.', 'tutor' ); ?>" id="import_options"><?php esc_html_e( 'Update Settings', 'tutor' ); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32 item-variation-table settings-history">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_attr_e( 'Settings History', 'tutor' ); ?></div>
	</div>
	<div class="item-wrapper history_data">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<?php esc_attr_e( 'Date', 'tutor' ); ?>
			</div>
		</div>
		<?php if ( $tutor_options = get_option( 'tutor_settings_log', array() ) ) : ?>
			<?php
			foreach ( $tutor_options as $key => $option_data ) :
				$datetypeClass = 'saved' == $option_data['datatype'] ? ' label-primary' : ' label-refund';
				?>
				<div class="tutor-option-field-row">
					<div class="tutor-option-field-label">
						<div class="tutor-fs-7 tutor-fw-medium">
							<?php echo esc_html( $option_data['history_date'] ); ?>
							<span class="tutor-badge-label tutor-ml-16<?php echo esc_attr( $datetypeClass ); ?>"> <?php echo esc_html( ucwords( $option_data['datatype'] ) ); ?></span>
						</div>
					</div>
					<div class="tutor-option-field-input">
						<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm apply_settings" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="<?php esc_attr_e( 'Yes, Restore Settings', 'tutor' ); ?>" data-heading="<?php esc_attr_e( 'Restore Previous Settings?', 'tutor' ); ?>" data-message="<?php esc_attr_e( 'WARNING! This will overwrite all existing settings, please proceed with caution.', 'tutor' ); ?>" data-id="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Apply', 'tutor' ); ?></button>
						<div class="tutor-dropdown-parent tutor-ml-16">
							<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
								<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
							</button>
							<ul class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
								<li>
									<a href="javascript:;" class="tutor-dropdown-item export_single_settings" data-id="<?php echo esc_attr( $key ); ?>">
										<span class="tutor-icon-archive tutor-mr-8" area-hidden="true"></span>
										<span><?php esc_html_e( 'Download', 'tutor' ); ?></span>
									</a>
								</li>
								<li>
									<a href="javascript:;" class="tutor-dropdown-item delete_single_settings" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="<?php esc_attr_e( 'Yes, Delete Settings', 'tutor' ); ?>" data-heading="<?php esc_attr_e( 'Delete This Settings?', 'tutor' ); ?>" data-message="<?php esc_attr_e( 'WARNING! This will remove the settings history data from your system, please proceed with caution.', 'tutor' ); ?>" data-id="<?php echo esc_attr( $key ); ?>">
										<span class="icon tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></span>
										<span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="tutor-option-field-row">
				<div class="tutor-option-field-label">
					<div class="tutor-fs-7 tutor-fw-medium"><?php esc_html_e( 'No settings data found.', 'tutor' ); ?></div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Reset Settings', 'tutor' ); ?></div>
	</div>
	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<div class="tutor-fs-6 tutor-color-black"><?php esc_html_e( 'Reset Everything to Default', 'tutor' ); ?></div>
				<div class="tutor-fs-7 tutor-fw-medium tutor-color-secondary">
					<span class="tutor-fw-medium"><?php esc_html_e( 'It will revert all settings to initial setup.', 'tutor' ); ?></span>
				</div>
			</div>
			<div class="tutor-option-field-input">
				<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-reset-all" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="<?php esc_attr_e( 'Yes, Reset Settings', 'tutor' ); ?>" data-heading="<?php esc_attr_e( 'Reset All Settings?', 'tutor' ); ?>" data-message="<?php esc_attr_e( 'WARNING! This will reset all settings to default, please proceed with caution.', 'tutor' ); ?>" id="reset_options"><?php esc_html_e( 'Reset All Settings', 'tutor' ); ?></button>
			</div>
		</div>
	</div>
</div>
