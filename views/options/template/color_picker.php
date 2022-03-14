<?php

/**
 * Color picker template for settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */
$fields_groups = is_array( $blocks['fields_group'] ) ? $blocks['fields_group'] : array();
?>
<div class="tutor-option-single-item <?php echo esc_attr( $blocks['slug'] ); ?>">
	<?php echo ( isset( $blocks['label'] ) && ! empty( $blocks['label'] ) ) ? '<h4>' . esc_attr( $blocks['label'] ) . '</h4>' : ''; ?>
	<div class="item-grid">
		<div class="item-wrapper color-preset-picker">
			<?php
			foreach ( $fields_groups as $fields_group ) {
				if ( 'color_preset' === $fields_group['type'] ) {
					$preset_color_fields = $fields_group['fields'];
					?>
					<div class="tutor-option-field-row tutor-d-block">
						<div class="tutor-option-field-label">
							<h5 class="label"><?php echo esc_attr( $fields_group['label'] ); ?></h5>
							<p class="desc"><?php echo esc_attr( $fields_group['desc'] ); ?></p>
						</div>
						<div class="tutor-option-field-input color-preset-grid">
							<?php
							foreach ( $preset_color_fields as $fields ) {
								$option_value  = $this->get( $fields_group['key'], tutils()->array_get( 'default', $fields_group ) );
								$option_value  = ! isset( $option_value ) || empty( $option_value ) ? 'custom' : $option_value;
								$preset_colors = tutils()->sanitize_array( $fields['colors'] );

								?>
								<label for="<?php echo esc_attr( $fields['key'] ); ?>" class="color-preset-input">
									<input type="radio" name="tutor_option[<?php echo esc_attr( $fields_group['key'] ); ?>]"
														id="<?php echo esc_attr( $fields['key'] ); ?>"
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
											<span class="text-regular-body"><?php echo esc_attr( $fields['label'] ); ?></span><span class="check-icon"></span>
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
								echo $this->generate_field( tutils()->sanitize_array( $field ) );
							}
						}
						?>
						<div class="other_colors">
							<?php
							foreach ( $fields_group['fields'] as $key => $field ) {
								if ( false === $field['preset_exist'] ) {
									echo $this->generate_field( tutils()->sanitize_array( $field ) );
								}
							}
							?>
						</div>
					</div>
					<?php
				}
			}
			?>
			<div class="more_button tutor-font-size-16">
				<i class="tutor-icon-plus-filled"></i>
				<span>Show More</span>
			</div>
		</div>
	</div>
</div>
