<?php
/**
 * Settings log page
 *
 * @package Tutor\Views
 * @subpackage Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.6.0
 */

?>
<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black">
		<?php esc_html_e( 'Settings Log', 'tutor' ); ?>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32">
	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<div class="label tutor-fs-6 tutor-color-black"><?php esc_html_e( 'Current Settings', 'tutor' ); ?></div>
				<div class="desc tutor-fs-8 tutor-color-subdued">
					<span class="tutor-fw-medium"><?php esc_html_e( 'Last Update', 'tutor' ); ?>: </span> <?php echo esc_html( get_option( 'tutor_option_update_time' ) ); ?>
				</div>
			</div>
			<div class="tutor-option-field-input">
				<button class="tutor-btn tutor-btn-primary tutor-btn-sm" id="tutor_export_settings"><?php esc_html_e( 'Export Settings', 'tutor' ); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32 item-variation-table settings-history">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_attr_e( 'Logs', 'tutor' ); ?></div>
	</div>
	<div class="item-wrapper history_data">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<?php esc_attr_e( 'Date', 'tutor' ); ?>
			</div>
		</div>
		<?php
		$tutor_options = get_option( 'tutor_settings_log', array() );
		if ( $tutor_options ) :
			?>
			<?php
			foreach ( $tutor_options as $key => $option_data ) :
				$datetype_class = ' label-default';
				if ( 'saved' === $option_data['datatype'] ) {
					$datetype_class = ' label-primary';
				} if ( 'Imported' === $option_data['datatype'] ) {
					$datetype_class = ' label-success';
				}
				?>
				<div class="tutor-option-field-row">
					<div class="tutor-option-field-label">
						<div class="tutor-fs-7 tutor-fw-medium">
							<?php echo esc_html( $option_data['history_date'] ); ?>
							<span class="tutor-badge-label tutor-ml-16<?php echo esc_attr( $datetype_class ); ?>"> <?php echo esc_html( ucwords( $option_data['datatype'] ) ); ?></span>
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
				<div class="tutor-fs-6 tutor-color-black tutor-mb-4"><?php esc_html_e( 'Restore to Default Settings', 'tutor' ); ?></div>
				<div class="tutor-fs-7 tutor-color-subdued">
					<?php esc_html_e( 'Revert all settings back to their initial state.', 'tutor' ); ?>
				</div>
			</div>
			<div class="tutor-option-field-input">
				<button class="tutor-btn tutor-btn-outline-primary tutor-btn-sm tutor-reset-all" 
						data-tutor-modal-target="tutor-modal-bulk-action" 
						data-btntext="<?php esc_attr_e( 'Yes, Reset Settings', 'tutor' ); ?>" 
						data-heading="<?php esc_attr_e( 'Reset All Settings?', 'tutor' ); ?>" 
						data-message="<?php esc_attr_e( 'WARNING! This will reset all settings to default, please proceed with caution.', 'tutor' ); ?>" 
						id="tutor_reset_options"><?php esc_html_e( 'Reset All Settings', 'tutor' ); ?></button>
			</div>
		</div>
	</div>
</div>
