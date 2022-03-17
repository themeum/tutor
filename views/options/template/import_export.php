<div class="tutor-option-main-title">
	<h2><?php _e('Import/Export','tutor'); ?></h2>
</div>

<?php
tutor_alert(
	__( 'Warning: Importing, Restoring, or Resetting will overwrite ALL existing settings. Please proceed with caution.', 'tutor' ),
	'warning'
);
// pr(get_option( 'tutor_default_option' ));
// pr(get_option( 'tutor_option' ));
?>



<div class="tutor-option-single-item">
	<h4><?php _e('Export','tutor'); ?></h4>
	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<div class="label tutor-fs-6 tutor-fw-normal tutor-color-black"><?php _e('Current Settings','tutor'); ?></div>
				<div class="desc tutor-fs-8 tutor-fw-medium tutor-color-black-60">
					<span style="font-weight: 500"><?php _e('Last Update','tutor'); ?>: </span> <?php echo get_option('tutor_option_update_time'); ?>
				</div>
			</div>
			<div class="tutor-option-field-input">
				<button class="tutor-btn tutor-is-outline tutor-is-sm" id="export_settings"><?php _e('Export Settings','tutor'); ?></button>
			</div>
		</div>
	</div>
</div>


<div class="tutor-option-single-item item-variation-dragndrop import-setting">
	<h4><?php _e('Import Settings','tutor'); ?></h4>

	<div class="item-wrapper">
		<div class="tutor-option-field-row tutor-d-block d-block">
			<div class="tutor-option-field-label">
				<div class="drag-drop-zone">
					<span class="tutor-icon-upload-icon-line tutor-icon-80 tutor-color-brand-wordpress"></span>
					<div class="title"><?php _e('Drag &amp; Drop your JSON File here','tutor'); ?> </div>
					<div class="subtitle"><span><?php _e('File Format','tutor'); ?>:</span> .json <br> <?php _e('Or','tutor'); ?></div>
					<label for="drag-drop-input" class="tutor-btn tutor-is-sm">
						<input type="file" name="drag-drop-input" id="drag-drop-input" accept="">
						<span><?php _e('Browse File','tutor'); ?></span>
					</label>
					<span class="file-info"></span>
				</div>
			</div>
			<div class="tutor-option-field-input tutor-mt-16">
				<button class="tutor-btn tutor-is-sm tutor_import_options" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="<?php _e('Yes, Import Settings','tutor'); ?>" data-heading="<?php _e('Import from Previous Settings?','tutor'); ?>" data-message="<?php _e('WARNING! This will overwrite all existing settings, please proceed with caution.','tutor'); ?>" id="import_options"><?php _e('Update Settings','tutor'); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="tutor-option-single-item item-variation-table settings-history">
	<h4><?php _e('Settings History','tutor'); ?></h4>
	<div class="item-wrapper history_data">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<p><?php _e('Date','tutor'); ?></p>
			</div>
		</div>
		<?php if ( $tutor_options = get_option( 'tutor_settings_log', array() ) ) : ?>
			<?php
			foreach ( $tutor_options as $key => $option_data ) :
				$datetypeClass = $option_data['datatype'] == 'saved' ? ' label-primary-wp' : ' label-refund';
				?>
				<div class="tutor-option-field-row">
					<div class="tutor-option-field-label">
						<p class="text-medium-small"><?php echo esc_html( $option_data['history_date'] ); ?>
						<span class="tutor-badge-label tutor-ml-16<?php echo $datetypeClass; ?>"> <?php echo esc_html( ucwords( $option_data['datatype'] ) ); ?></span> </p>
					</div>
					<div class="tutor-option-field-input">
						<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs apply_settings" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="<?php _e('Yes, Restore Settings','tutor'); ?>" data-heading="<?php _e('Restore Previous Settings?','tutor'); ?>" data-message="<?php _e('WARNING! This will overwrite all existing settings, please proceed with caution.','tutor'); ?>" data-id="<?php echo $key; ?>"><?php _e('Apply','tutor'); ?></button>
						<div class="tutor-popup-opener tutor-ml-16">
							<button
							type="button"
							class="popup-btn"
							data-tutor-popup-target="popup-<?php echo esc_attr( $key ); ?>"
							>
								<span class="toggle-icon"></span>
							</button>
							<ul id="popup-<?php echo esc_attr( $key ); ?>" class="popup-menu">
							<li>
								<a class="export_single_settings" data-id="<?php echo $key; ?>">
									<span class="icon tutor-icon-msg-archive-filled tutor-color-design-white"></span>
									<span class="text-regular-body tutor-color-white"><?php _e('Download','tutor'); ?></span>
								</a>
							</li>
							<li>
								<a class="delete_single_settings"  data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="<?php _e('Yes, Delete Settings','tutor'); ?>" data-heading="<?php _e('Delete This Settings?','tutor'); ?>" data-message="<?php _e('WARNING! This will remove the settings history data from your system, please proceed with caution.','tutor'); ?>" data-id="<?php echo $key; ?>">
									<span class="icon tutor-icon-delete-fill-filled tutor-color-design-white"></span>
									<span class="text-regular-body tutor-color-white"><?php _e('Delete','tutor'); ?></span>
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
					<p class="text-medium-small"><?php echo __( 'No settings data found.', 'tutor' ); ?></p>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>


<div class="tutor-option-single-item">
	<h4><?php _e('Reset Settings','tutor'); ?></h4>
	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<div class="text-regular-h6 tutor-color-black"><?php _e('Reset Everything to Default','tutor'); ?></div>
				<div class="text-medium-small tutor-color-black-60">
					<span style="font-weight: 500"> <?php _e('It will revert all settings to initial setup.','tutor'); ?>
				</span></div>
			</div>
			<div class="tutor-option-field-input">
				<button class="tutor-btn tutor-is-outline tutor-is-sm tutor-reset-all" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="<?php _e('Yes, Reset Settings','tutor'); ?>" data-heading="<?php _e('Reset All Settings?','tutor'); ?>" data-message="<?php _e('WARNING! This will reset all settings to default, please proceed with caution.','tutor') ?>" id="reset_options"><?php _e('Reset All Settings','tutor'); ?></button>
			</div>
		</div>
	</div>
</div>