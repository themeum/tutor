<div class="tutor-option-main-title">
	<h2>Import/Export</h2>
</div>
<div class="tutor-backend-alert alert-warning">
	<span class="alert-icon tutor-v2-icon-test icon-warning-filled"></span>
	<span class="alert-content">Warning: Importing, Restoring, or Resetting will overwrite ALL existing settings. Please proceed with caution.</span>
</div>

<div class="tutor-option-single-item item-variation-dragndrop import-setting">
	<h4>Import Settings</h4>

	<div class="item-wrapper">
		<div class="tutor-option-field-row tutor-d-block">
			<div class="tutor-option-field-label">
				<div class="drag-drop-zone">
					<span class="icon tutor-v2-icon-test icon-upload-icon-line"></span>
					<div class="title">Drag &amp; Drop your JSON File here </div>
					<div class="subtitle"><span>File Format:</span> .json <br> Or</div>
					<label for="drag-drop-input" class="tutor-btn tutor-is-sm">
						<input type="file" name="drag-drop-input" id="drag-drop-input" accept="">
						<span>Browse File</span>
					</label>
					<span class="file-info"></span>
				</div>
			</div>
			<div class="tutor-option-field-input">
				<button class="tutor-btn tutor-is-sm" id="import_options">Update Settings</button>
			</div>
		</div>
	</div>
</div>

<div class="tutor-option-single-item">
	<h4>Export</h4>
	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<p class="text-regular-h6">Current Settings</p>
				<p class="text-medium-small">
					<span style="font-weight: 500">Last Update: </span>20 July, 2020, 12:47 pm
				</p>
			</div>
			<div class="tutor-option-field-input">
				<button class="tutor-btn tutor-is-outline tutor-is-sm" id="export_settings">Export Settings</button>
			</div>
		</div>
	</div>
</div>

<div class="tutor-option-single-item item-variation-table settings-history">
	<h4>Settings History</h4>
	<div class="item-wrapper history_data">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<p>Date</p>
			</div>
		</div>
		<?php if ( $tutor_options = get_option( 'tutor_settings_log', array() ) ) : ?>
			<?php foreach ( $tutor_options as $key => $option_data ) : ?>
				<div class="tutor-option-field-row">
					<div class="tutor-option-field-label">
						<p class="text-medium-small"><?php echo esc_html( $option_data['history_date'] ); ?>
						<span className="tutor-badge-label<?php echo $datetypeClass; ?>"> <?php echo esc_html( ucwords( $option_data['datatype'] ) ); ?></span> </p>
					</div>
					<div class="tutor-option-field-input">
						<button class="tutor-btn tutor-is-outline tutor-is-default tutor-is-xs apply_settings" data-id="<?php echo $key; ?>">Apply</button>
						<div class="popup-opener">
							<button type="button" class="popup-btn">
								<span class="toggle-icon"></span>
							</button>
							<ul class="popup-menu">
								<li>
									<a class="export_single_settings" data-id="<?php echo $key; ?>">
										<span class="icon tutor-v2-icon-test icon-msg-archive-filled"></span>
										<span>Download</span>
									</a>
								</li>
								<li>
									<a class="delete_single_settings" data-id="<?php echo $key; ?>">
										<span class="icon tutor-v2-icon-test icon-delete-fill-filled"></span>
										<span>Delete</span>
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
	<h4>Reset Settings</h4>
	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<p class="text-regular-h6"> Reset Everything to Default</p>
				<p class="text-medium-small">
					<span style="font-weight: 500">Reset subtitle
				</span></p>
			</div>
			<div class="tutor-option-field-input">
				<button class="tutor-btn tutor-is-outline tutor-is-sm" id="reset_options">Reset All Settings</button>
			</div>
		</div>
	</div>
</div>
