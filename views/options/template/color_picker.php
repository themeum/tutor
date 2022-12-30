<?php
/**
 * Color picker template for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$fields_groups = is_array( $blocks['fields_group'] ) ? $blocks['fields_group'] : array();

?>
<div class="tutor-option-single-item tutor-mb-32 <?php echo esc_attr( $blocks['slug'] ); ?>">
	<?php if ( isset( $blocks['label'] ) ) : ?>
		<div class="tutor-option-group-title tutor-mb-16">
			<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_attr( $blocks['label'] ); ?></div>
		</div>
	<?php endif; ?>
	<div class="item-grid">
		<div class="item-wrapper color-preset-picker">
			<div class="tutor-toggle-more-content tutor-toggle-more-collapsed" data-tutor-toggle-more-content data-toggle-height="600" style="height: 600px;">
				<?php
				foreach ( $fields_groups as $fields_group ) {
					if ( 'color_preset' === $fields_group['type'] ) {
						$preset_color_fields = $fields_group['fields'];
						?>
						<div class="tutor-option-field-row tutor-d-block">
							<div class="tutor-option-field-label">
								<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name><?php echo esc_attr( $fields_group['label'] ); ?></div>
								<div class="tutor-fs-7 tutor-color-muted"><?php echo esc_attr( $fields_group['desc'] ); ?></div>
							</div>
							<div class="tutor-option-field-input color-preset-grid">
								<?php
								$is_color_preset = $this->get( $fields_group['key'] );

								foreach ( $preset_color_fields as $fields ) {
									$option_value  = $this->get( $fields_group['key'], tutils()->array_get( 'default', $fields_group ) );
									$option_value  = ! isset( $option_value ) || empty( $option_value ) || false == $is_color_preset ? 'custom' : $option_value;
									$preset_colors = tutils()->sanitize_array( $fields['colors'] );

									?>
									<label for="tutor_preset_<?php echo esc_attr( $fields['key'] ); ?>" class="color-preset-input">
										<input type="radio" name="tutor_option[<?php echo esc_attr( $fields_group['key'] ); ?>]"
															id="tutor_preset_<?php echo esc_attr( $fields['key'] ); ?>"
															value="<?php echo esc_attr( $fields['key'] ); ?>" <?php esc_attr( checked( $option_value, $fields['key'] ) ); ?>>
										<div class="preset-item">
											<div class="header">
												<?php
												foreach ( $preset_colors as $key => $color ) {
													$get_color   = ( 'custom' !== $option_value && 'custom' === $fields['key'] ) ?
														esc_attr( $color['value'] ) :
														esc_attr( $this->get( $color['slug'] ) );
													$color_value = 'custom' === $fields['key'] ? esc_attr( $get_color ) : esc_attr( $color['value'] );
													$preset_name = (string) esc_attr( $color['preset_name'] );
													?>
													<span <?php echo 3 < $key ? 'class="hidden"' : ''; ?>
														data-preset="<?php echo esc_attr( $preset_name ); ?>"
														data-color="<?php echo esc_attr( $color_value ); ?>"
														style="background-color: <?php echo esc_attr( $color_value ); ?>;"></span>
													<?php
												}
												?>
											</div>
											<div class="footer">
												<span class="tutor-fs-6"><?php echo esc_attr( $fields['label'] ); ?></span><span class="check-icon"></span>
											</div>
										</div>
									</label>
								<?php } ?>
							</div>
						</div>
						<?php
					}

					if ( 'color_fields' === $fields_group['type'] ) {
						?>
						<div class="color-picker-wrapper">
							<?php
							foreach ( $fields_group['fields'] as $key => $field ) {
								if ( true === $field['preset_exist'] ) {
									//phpcs:ignore -- contain safe data
									echo $this->generate_field( tutils()->sanitize_array( $field ) );
								}
							}
							?>
								<?php
								foreach ( $fields_group['fields'] as $key => $field ) {
									if ( false === $field['preset_exist'] ) {
										//phpcs:ignore -- contain safe data
										echo $this->generate_field( tutils()->sanitize_array( $field ) );
									}
								}
								?>
						</div>
						<?php
					}
				}
				?>
			</div>

			<div class="tutor-text-center tutor-mt-32">
				<a href="#" class="tutor-btn-show-more tutor-btn tutor-btn-ghost" data-tutor-toggle-more=".tutor-toggle-more-content">
					<span class="tutor-toggle-btn-icon tutor-icon tutor-icon-plus tutor-mr-8" area-hidden="true"></span>
					<span class="tutor-toggle-btn-text"><?php esc_html_e( 'Show More', 'tutor' ); ?></span>
				</a>
			</div>
		</div>
	</div>
</div>
