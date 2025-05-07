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

<!-- An array for export checkbox with name and label, cna be nested and will use it for rendering in modal -->
<?php
$default_checkbox_value = '1';

$export_checkboxes = array(
	'courses'         => array(
		'label'          => __( 'Courses', 'tutor' ),
		'count'          => 0,
		'name'           => 'courses',
		'value'          => $default_checkbox_value,
		'keep_user_data' => $default_checkbox_value,
		'children'       => array(
			'lessons'     => array(
				'label' => __( 'Lessons', 'tutor' ),
				'count' => 0,
				'name'  => 'lessons',
				'value' => $default_checkbox_value,
			),
			'quizzes'     => array(
				'label' => __( 'Quizzes', 'tutor' ),
				'count' => 0,
				'name'  => 'quizzes',
				'value' => $default_checkbox_value,
			),
			'assignments' => array(
				'label' => __( 'Assignments', 'tutor' ),
				'count' => 0,
				'name'  => 'assignments',
				'value' => $default_checkbox_value,
			),
			'resources'   => array(
				'label' => __( 'Resources', 'tutor' ),
				'count' => 0,
				'name'  => 'resources',
				'value' => $default_checkbox_value,
			),
		),
	),
	'bundles'         => array(
		'label'          => __( 'Bundles', 'tutor' ),
		'count'          => 0,
		'name'           => 'bundles',
		'value'          => $default_checkbox_value,
		'keep_user_data' => $default_checkbox_value,
	),
	'users'           => array(
		'label' => __( 'Users List', 'tutor' ),
		'count' => tutor_utils()->get_users_count(),
		'name'  => 'users',
		'count' => 0,
		'value' => $default_checkbox_value,
	),
	'global_settings' => array(
		'label' => __( 'Global Settings', 'tutor' ),
		'name'  => 'global_settings',
		'value' => $default_checkbox_value,
	),
);

/**
 * Render export checkboxes
 *
 * @param array $checkboxes checkboxes.
 * @return void
 */
function render_export_checkboxes( $checkboxes ) {
	?>
	<div class="item-wrapper setting-checkboxes tutor-mx-20 tutor-py-0">
		<?php
		foreach ( $checkboxes as $key => $checkbox ) {
			$label = $checkbox['label'];
			$count = $checkbox['count'];
			$name  = $checkbox['name'];
			?>
			<div class="tutor-option-field-row">
				<div class="tutor-px-20">
					<label class="tutor-form-check">
					<input type="checkbox" class="tutor-form-check-input" name="export_<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $checkbox['value'] ); ?>" <?php checked( '1', $checkbox['value'] ); ?>> <?php 
							if ( isset($count) && (is_numeric($count) || (!empty($count) && is_string($count)))) {
								// translators: %s: label, %d: count
								echo sprintf( __( '%s (%d)', 'tutor' ), esc_html( $label ), esc_html( $count ) );
							} else {
								echo esc_html( $label );
							}
						?>
					</label>
					<?php
					if ( $checkbox['value'] && isset( $checkbox['children'] ) && is_array( $checkbox['children'] ) ) {
						?>
						<div class="item-wrapper tutor-mt-8 tutor-mb-0">
							<div class="children-row tutor-px-16">
								<?php
								foreach ( $checkbox['children'] as $child_key => $child_checkbox ) {
									$child_label = $child_checkbox['label'];
									$child_count = $child_checkbox['count'];
									$child_name  = $child_checkbox['name'];
									?>
									<label class="tutor-form-check">
										<input type="checkbox" class="tutor-form-check-input" name="export_<?php echo esc_attr( $name ); ?>_<?php echo esc_attr( $child_name ); ?>" value="<?php echo esc_attr( $child_checkbox['value'] ); ?>" <?php checked( '1', $child_checkbox['value'] ); ?>><?php echo sprintf( __( '%s (%d)', 'tutor' ), esc_html( $child_label ), esc_html( $child_count ) ); ?>
									</label>
									<?php
								}
								?>
							</div>

							<?php
							if ( isset( $checkbox['keep_user_data'] ) ) {
								?>
								<div class="children-row" style="background-color: rgba(var(--tutor-color-primary-rgb), 0.03);">
									<label class="tutor-form-check">
										<input type="checkbox" class="tutor-form-check-input" name="export_<?php echo esc_attr( $name ); ?>_keep_user_data" value="<?php echo esc_attr( $checkbox['keep_user_data'] ); ?>" <?php checked( '1', $checkbox['keep_user_data'] ); ?>> <?php esc_html_e( 'Keep User Data', 'tutor' ); ?>
									</label>
								</div>
								<?php
							}
							?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}
?>

<div class="tutor-option-main-title">
	<div class="tutor-fs-4 tutor-fw-medium tutor-color-black">
		<?php esc_html_e( 'Import/Export', 'tutor' ); ?>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32 item-variation-dragndrop import-setting">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Import', 'tutor' ); ?></div>
	</div>
	<div class="item-wrapper tutor-import-drag-drop">
		<div class="tutor-option-field-row tutor-d-block">
			<div>
				<div class="drag-drop-zone">
					<span class="tutor-icon-upload tutor-fs-1 tutor-color-primary"></span>
					<div>
						<label for="drag-drop-input" class="tutor-btn tutor-btn-secondary tutor-btn-sm tutor-mt-8">
							<input type="file" name="drag-drop-input" id="drag-drop-input" class="tutor-d-none import-settings" accept=".json" />
							<span><?php esc_html_e( 'Choose a file', 'tutor' ); ?></span>
						</label>
						<span class="file-info tutor-d-none"></span>
					</div>
					<div class="subtitle">
						<span>
							<?php
							echo sprintf(
								/* translators: %s: file type */
								__( 'Supported format: %s', 'tutor' ),
								'.JSON'
							);
							?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_html_e( 'Export', 'tutor' ); ?></div>
	</div>

	<div class="item-wrapper">
		<div class="tutor-option-field-row">
			<div class="tutor-option-field-label">
				<div class="label tutor-fs-6 tutor-color-black"><?php esc_html_e( 'Export Data', 'tutor' ); ?></div>
				<div class="desc tutor-fs-8 tutor-fw-medium tutor-color-secondary">
					<span><?php esc_html_e( 'Easily export your courses, lessons, quizzes, user data, and global settings.', 'tutor' ); ?>
				</div>
			</div>
			<div class="tutor-option-field-input">
				<button class="tutor-btn tutor-btn-primary tutor-btn-sm" data-tutor-modal-target="tutor-export-data-modal">
					<i class="btn-icon tutor-icon-export tutor-mr-4"></i>
					<?php esc_html_e( 'Initiate Export', 'tutor' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<div class="tutor-option-single-item tutor-mb-32 item-variation-table">
	<div class="tutor-option-group-title tutor-mb-16">
		<div class="tutor-fs-6 tutor-color-muted"><?php esc_attr_e( 'History', 'tutor' ); ?></div>
	</div>
	<div class="tutor-table-responsive">
		<table class="tutor-table table-import-export-history">
			<thead>
				<tr>
					<th>
						<?php esc_attr_e( 'Title', 'tutor' ); ?>
					</th>
					<th>
						<?php esc_attr_e( 'Type', 'tutor' ); ?>
					</th>
					<th>
						<?php esc_attr_e( 'Author', 'tutor' ); ?>
					</th>
					<th>
						<?php esc_attr_e( 'Date', 'tutor' ); ?>
						<i class="tutor-icon-sort" aria-hidden="true"></i>
					</th>
					<th />
				</tr>
			</thead>
			<tbody>
				<?php
				$tutor_import_export_history = 
				// Mock data for testing
				array(
					'1' => array(
						'title'      => 'Courses',
						'author'     => 'John Doe',
						'date'       => '2023-10-01 12:00am',
						'type'       => 'export',
						'is_setting' => false,
					),
					'2' => array(
						'title'      => 'Lessons',
						'author'     => 'Jane Smith',
						'date'       => '2023-10-02 1:00pm',
						'type'       => 'import',
						'is_setting' => false,
					),
					'3' => array(
						'title'      => 'Settings',
						'author'     => 'Alice Johnson',
						'date'       => '2023-10-03 2:00pm',
						'type'       => 'export',
						'is_setting' => true,
					),
				);
				if ( $tutor_import_export_history ) :
					foreach ( $tutor_import_export_history as $key => $option_data ) :
						?>
						<tr>
							<td>
								<div class="tutor-fs-7 tutor-fw-medium">
									<?php echo esc_html( $option_data['title'] ); ?>
								</div>
							</td>
							<td>
								<?php
								$type_icon  = 'import' === $option_data['type'] ? 'import' : 'export';
								$type_class = 'import' === $option_data['type'] ? 'tutor-color-primary' : 'tutor-color-secondary';
								$type       = 'import' === $option_data['type'] ? esc_html__( 'Imported', 'tutor' ) : esc_html__( 'Exported', 'tutor' );
								?>
								<div class="tutor-fs-7 tutor-fw-medium tutor-text-c <?php echo esc_attr( $type_class ); ?>">
									<i class="tutor-icon-<?php echo esc_attr( $option_data['type'] ); ?> tutor-mr-4 <?php echo esc_attr( $type_icon ); ?>" aria-hidden="true"></i>
									<?php echo esc_html( $type ); ?>
								</div>
							</td>
							<td>
								<div class="tutor-fs-7 tutor-fw-medium">
									<?php echo esc_html( $option_data['author'] ); ?>
								</div>
							</td>
							<td>
								<div class="tutor-fs-7 tutor-fw-normal">
									<?php echo esc_html( $option_data['date'] ); ?>
								</div>
							</td>
							<td>
								<div class="tutor-dropdown-parent tutor-ml-16">
									<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
										<span class="tutor-icon-kebab-menu" aria-hidden="true"></span>
									</button>
									<ul class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
										<?php
										if ( $option_data['is_setting'] ) {
											?>
											<li>
												<a href="javascript:;" class="tutor-dropdown-item apply_settings" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="<?php esc_attr_e( 'Yes, Restore Settings', 'tutor' ); ?>" data-heading="<?php esc_attr_e( 'Restore Previous Settings?', 'tutor' ); ?>" data-message="<?php esc_attr_e( 'WARNING! This will overwrite all existing settings, please proceed with caution.', 'tutor' ); ?>" data-id="<?php echo esc_attr( $key ); ?>">
													<span class="tutor-mr-8" aria-hidden="true">✓</span>
													<span><?php esc_html_e( 'Apply Settings', 'tutor' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
										<li>
											<a href="javascript:;" class="tutor-dropdown-item export_single_settings" data-id="<?php echo esc_attr( $key ); ?>">
												<span class="tutor-icon-archive tutor-mr-8" aria-hidden="true"></span>
												<span><?php esc_html_e( 'Download', 'tutor' ); ?></span>
											</a>
										</li>
										<li>
											<a href="javascript:;" class="tutor-dropdown-item delete_single_settings" data-tutor-modal-target="tutor-modal-bulk-action" data-btntext="<?php esc_attr_e( 'Yes, Delete Settings', 'tutor' ); ?>" data-heading="<?php esc_attr_e( 'Delete This Settings?', 'tutor' ); ?>" data-message="<?php esc_attr_e( 'WARNING! This will remove the settings history data from your system, please proceed with caution.', 'tutor' ); ?>" data-id="<?php echo esc_attr( $key ); ?>">
												<span class="icon tutor-icon-trash-can-bold tutor-mr-8" aria-hidden="true"></span>
												<span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
											</a>
										</li>
									</ul>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="tutor-option-field-row">
						<div class="tutor-option-field-label">
							<div class="tutor-fs-7 tutor-fw-medium"><?php esc_html_e( 'No settings data found.', 'tutor' ); ?></div>
						</div>
					</div>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Export Data Modal -->
<div id="tutor-export-data-modal" class="tutor-modal tutor-modal-scrollable">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<form id="tutor-export-data-form" class="tutor-modal-content" autocomplete="off" method="post">
			<div class="tutor-modal-header">
				<div class="tutor-modal-title">
					<span class="tutor-color-primary tutor-fw-medium"><?php esc_html_e( 'Exporter', 'tutor' ); ?></span>

					<button class="tutor-btn tutor-btn-primary tutor-btn-sm" data-tutor-modal-target="tutor-export-data-modal">
						<i class="tutor-icon-export tutor-mr-4" aria-hidden="true"></i>
						<?php esc_html_e( 'Export', 'tutor' ); ?>
					</button>
				</div>
				<button class="tutor-iconic-btn tutor-modal-close tutor-flex-shrink-0" data-tutor-modal-close>
					<span class="tutor-icon-times" aria-hidden="true"></span>
				</button>
			</div>

			<div class="tutor-modal-body">
				<?php tutor_nonce_field(); ?>
				<input type="hidden" name="action" value="tutor_export_settings">
				<input type="hidden" name="export_type" value="">
				<input type="hidden" name="export_id" value="">
				<div class="tutor-option-single-item tutor-mb-32">
					<div class="item-wrapper">
						<div class="tutor-fs-7 tutor-color-black tutor-mb-16 tutor-mx-20">
							<?php esc_html_e( 'What do you want to export?', 'tutor' ); ?>
						</div>
						<?php echo render_export_checkboxes( $export_checkboxes ); ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Import Data Modal -->
<div id="tutor-import-data-modal" class="tutor-modal tutor-modal-scrollable">
	<div class="tutor-modal-overlay"></div>
	<div class="tutor-modal-window">
		<form id="tutor-import-data-form" class="tutor-modal-content" autocomplete="off" method="post">
			<div id="import-initial">
				<div class="tutor-modal-header">
					<div class="tutor-modal-title">
						<span class="tutor-fw-medium tutor-fs-7"><?php esc_html_e( 'Import CSV', 'tutor' ); ?></span>
					</div>
					<button class="tutor-iconic-btn tutor-modal-close tutor-flex-shrink-0" data-tutor-modal-close>
						<span class="tutor-icon-times" aria-hidden="true"></span>
					</button>
				</div>

				<div class="tutor-modal-body">
					<div class="tutor-d-flex tutor-flex-column tutor-mb-20 tutor-gap-1">
						<label class="tutor-d-flex tutor-align-center tutor-justify-between">
							<span>
								<?php esc_html_e( 'Validated', 'tutor' ); ?>
							</span>
							<div class="tutor-badge-label label-success  tutor-d-none" id="validation-status"></div>
						</label>
						<div class="attached-file-info">
							<span class="file-icon">
								<i class="tutor-icon-file-json" aria-hidden="true"></i>
							</span>

							<span class="file-name-and-action">
								<span class="file-name-and-size tutor-d-flex tutor-flex-column">
									<span class="file-name tutor-fw-medium tutor-fs-8" id="file-name"></span>
									<span class="file-size tutor-fs-8" id="file-size"></span>
								</span>
								<label for="drag-drop-input" class="tutor-btn tutor-btn-secondary tutor-btn-sm tutor-mt-8">
									<input type="file" name="drag-drop-input" id="drag-drop-input" class="tutor-d-none import-settings" accept=".json" />
									<span><?php esc_html_e( 'Replace', 'tutor' ); ?></span>
								</label>
							</span>
						</div>
					</div>

					<div class="import-warning">
						<i class="tutor-icon-warning" aria-hidden="true"></i>
						<span class="tutor-fs-7 tutor-fw-medium">
							<?php esc_html_e( 'WARNING! This will overwrite all existing settings, please proceed with caution.', 'tutor' ); ?>
						</span>
					</div>
				</div>

				<div class="tutor-modal-footer">
					<div class="tutor-ml-auto">
						<button class="tutor-btn tutor-btn-text tutor-btn-sm" data-tutor-modal-close>
							<?php esc_html_e( 'Cancel', 'tutor' ); ?>
						</button>
						<button class="tutor-btn tutor-btn-primary tutor-btn-sm" id="tutor-import-data-btn">
							<?php esc_html_e( 'Import', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>

			<div id="import-inprogress" class="tutor-d-none">
				<div class="tutor-modal-body">
					<div class="tutor-d-flex tutor-justify-between tutor-align-center tutor-w-100">
						<div class="tutor-fs-7 tutor-color-black tutor-mb-16 tutor-mx-20">
							<?php esc_html_e( 'Importing...', 'tutor' ); ?>
						</div>

						<div class="tutor-badge-label label-success tutor-mb-16" id="import-status">
							<?php esc_html_e( 'In Progress', 'tutor' ); ?>
						</div>
					</div>
					<div class="tutor-text-secondary">
						<div id="file-name"></div>
					</div>
				</div>
			</div>

			<div id="import-success" class="tutor-d-none">
				<div class="tutor-modal-body tutor-text-center">
					<div class="tutor-d-flex tutor-flex-column tutor-justify-center tutor-align-center tutor-w-100">
						<div class="tutor-fs-5 tutor-color-black tutor-mb-16">
							<?php esc_html_e( 'Settings Import Successful!', 'tutor' ); ?>
						</div>

						<div class="tutor-fs-7 tutor-color-black tutor-mb-16">
							<?php echo sprintf( __('You have successfully imported a “%s”', 'tutor'), '<span id="file-name"></span>' ); ?>
						</div>
						<button class="tutor-btn tutor-btn-primary tutor-btn-sm" data-tutor-modal-close>
							<?php esc_html_e( 'Okay', 'tutor' ); ?>
						</button>
					</div>
				</div>
			</div>

			<div id="import-error" class="tutor-d-none">
				<div class="tutor-modal-header">
					<div class="tutor-modal-title">
						<span class="tutor-fw-medium tutor-fs-7"><?php esc_html_e( 'Import Error', 'tutor' ); ?></span>
					</div>
					<button class="tutor-iconic-btn tutor-modal-close tutor-flex-shrink-0" data-tutor-modal-close>
						<span class="tutor-icon-times" aria-hidden="true"></span>
					</button>
				</div>

				<div class="tutor-modal-body">
					<div class="tutor-fs-7 tutor-color-black tutor-mb-16 tutor-mx-20">
						<?php esc_html_e( 'An error occurred during import.', 'tutor' ); ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>