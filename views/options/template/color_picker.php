<div class="tutor-option-single-item <?php echo $blocks['slug']; ?>">
	<?php echo $blocks['label'] ? '<h4>' . $blocks['label'] . '</h4>' : ''; ?>
	<div class="item-grid">
		<?php // pr( $blocks ); ?>
		<div class="item-wrapper color-preset-picker">
		<?php foreach ( $blocks['fields_group'] as $fields_group ) : ?>
				<?php
				if ( $fields_group['type'] == 'preset_horizontal' ) :
					// pr( $fields_group );
					?>
					<div class="tutor-option-field-row d-block">
						<div class="tutor-option-field-label">
							<h5 class="label"><?php _e( $fields_group['label'], 'tutor-pro' ); ?></h5>
							<p class="desc"><?php _e( $fields_group['desc'], 'tutor-pro' ); ?></p>
						</div>
						<div class="tutor-option-field-input color-preset-grid">
							<?php foreach ( $fields_group['fields'] as $key => $fields ) : ?>
							<label for="<?php echo $key; ?>" class="color-preset-input preset-1">
								<input type="radio" name="color-preset" id="<?php echo $key; ?>" value="<?php echo $key; ?>" checked="">
								<div class="preset-item">
									<div class="header">
										<?php
										foreach ( $fields as $color ) :
											?>
										<span data-preset="<?php echo $color['preset_name']; ?>" style="background-color: <?php echo $color['value']; ?>;">1</span>
										<?php endforeach; ?>
									</div>
									<div class="footer">
										<span class="text-regular-body">Preset 1</span><span class="check-icon"></span>
									</div>
								</div>
							</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>


				<?php if ( $fields_group['type'] == 'color_fields' ) : ?>
					<div class="color-picker-wrapper">
					<?php foreach ( $fields_group['fields'] as $key => $fields ) : ?>
						<div class="tutor-option-field-row">
							<div class="tutor-option-field-label">
								<h5 class="label">Primary Color</h5>
								<p class="desc">These colors will be used throughout your website. Choose between these presets or create your own custom palette.</p>
							</div>
							<div class="tutor-option-field-input">
								<label for="color-picker-1" class="color-picker-input" data-key="primary" style="border-color: rgb(205, 207, 213); box-shadow: none;">
									<input type="color" name="color-picker-1" id="color-picker-1" value="#1973AA">
									<div class="picker-value text-regular-small">#000000</div>
								</label>
							</div>
						</div>
						<?php endforeach; ?>
						<div class="tutor-option-field-row">
							<div class="tutor-option-field-label">
								<h5 class="label">Primary Hover color</h5>
								<p class="desc">These colors will be used throughout your website. Choose between these presets or create your own custom palette.</p>
							</div>
							<div class="tutor-option-field-input">
								<label for="color-picker-2" class="color-picker-input" data-key="hover" style="border-color: rgb(205, 207, 213); box-shadow: none;">
									<input type="color" name="color-picker-2" id="color-picker-2" value="#5B616F">
									<div class="picker-value text-regular-small">#5d5d5d</div>
								</label>
							</div>
						</div>
						<div class="tutor-option-field-row">
							<div class="tutor-option-field-label">
								<h5 class="label">Text Color</h5>
								<p class="desc">These colors will be used throughout your website. Choose between these presets or create your own custom palette.</p>
							</div>
							<div class="tutor-option-field-input">
								<label for="color-picker-3" class="color-picker-input" data-key="text" style="border-color: rgb(205, 207, 213); box-shadow: none;">
									<input type="color" name="color-picker-3" id="color-picker-3" value="#C0C3CB">
									<div class="picker-value text-regular-small">#a2a2a2</div>
								</label>
							</div>
						</div>
						<div class="tutor-option-field-row">
							<div class="tutor-option-field-label">
								<h5 class="label">Light color</h5>
								<p class="desc">These colors will be used throughout your website. Choose between these presets or create your own custom palette.</p>
							</div>
							<div class="tutor-option-field-input">
								<label for="color-picker-4" class="color-picker-input" data-key="link" style="border-color: rgb(205, 207, 213); box-shadow: none;">
									<input type="color" name="color-picker-4" id="color-picker-4" value="#E3E6EB">
									<div class="picker-value text-regular-small">#d8d8d8</div>
								</label>
							</div>
						</div>
					</div>
				<?php endif; ?>




				<?php
				/*
				foreach ( $fields_group as $field ) : ?>
					<?php echo $this->generate_field( $field ); ?>
				<?php endforeach; */
				?>
		<?php endforeach; ?>
		</div>
	</div>
</div>
