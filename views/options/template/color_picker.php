<div class="tutor-option-single-item <?php echo $blocks['slug']; ?>">
	<?php echo $blocks['label'] ? '<h4>' . $blocks['label'] . '</h4>' : ''; ?>
	<div class="item-grid">
		<div class="item-wrapper color-preset-picker">
		<?php foreach ( $blocks['fields_group'] as $fields_group ) : ?>
				<?php
				if ( $fields_group['type'] == 'color_preset' ) :
					// pr( $fields_group );
					?>
					<div class="tutor-option-field-row d-block">
						<div class="tutor-option-field-label">
							<h5 class="label"><?php _e( $fields_group['label'], 'tutor-pro' ); ?></h5>
							<p class="desc"><?php _e( $fields_group['desc'], 'tutor-pro' ); ?></p>
						</div>
						<div class="tutor-option-field-input color-preset-grid">
							<?php
							foreach ( $fields_group['fields'] as $fields ) :
								$option_value = $this->get( $fields_group['key'], tutils()->array_get( 'default', $fields_group ) );
								// pr( $fields );
								?>
							<label for="<?php esc_attr_e( $fields['key'] ); ?>" class="color-preset-input">
								<input type="radio" name="tutor_option[<?php esc_attr_e( $fields_group['key'] ); ?>]" id="<?php esc_attr_e( $fields['key'] ); ?>" value="<?php esc_attr_e( $fields['key'] ); ?>" <?php checked( $option_value, $fields['key'] ); ?> >
								<div class="preset-item">
									<div class="header">
										<?php
										foreach ( $fields['colors'] as $color ) :
											$get_color   = ( $option_value != 'custom' && $fields['key'] == 'custom' ) ? $color['value'] : $this->get( $color['slug'] );
											$color_value = $fields['key'] == 'custom' ? $get_color : $color['value'];
											$preset_name = (string) $color['preset_name'];
											?>
										<span data-preset="<?php esc_attr_e( $preset_name, 'tutor' ); ?>" data-color="<?php echo $color_value; ?>" style="background-color: <?php echo $color_value; ?>;"></span>
										<?php endforeach; ?>
									</div>
									<div class="footer">
										<span class="text-regular-body"><?php esc_attr_e( $fields['label'] ); ?></span><span class="check-icon"></span>
									</div>
								</div>
							</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>


				<?php if ( $fields_group['type'] === 'color_fields' ) : ?>
					<div class="color-picker-wrapper">
						<?php foreach ( $fields_group['fields'] as $key => $field ) : ?>
							<?php echo $this->generate_field( $field ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

		<?php endforeach; ?>
		</div>
	</div>
</div>
