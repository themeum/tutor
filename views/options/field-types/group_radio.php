<?php
/**
 * Radio Group Settings
 *
 * @since v2.0.0
 * @package Radio
 */

$field_id = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row d-block">
	<?php require tutor()->path . 'views/options/template/field_heading.php'; ?>

	<div class="tutor-option-field-input" id="<?php echo $field_id; ?>">
		<div class="radio-thumbnail has-title instructor-list">
			<?php
			if ( ! empty( $field['group_options'] ) ) :
				foreach ( $field['group_options'] as $optkey => $options ) :
					?>
					<div class="<?php echo $optkey; ?>">
						<!-- <div class="layout-label"><?php // echo ucwords( $optkey ); ?></div> -->
						<div class="fields-wrapper">
							<?php
							foreach ( $options as $optionKey => $option ) :
								$option_value = $this->get( $field['key'], tutils()->array_get( 'default', $field ) );
								$field_key    = $optkey . '_' . $optionKey;
								?>
								<label for="<?php echo $optionKey; ?>">
									<input type="radio" name="tutor_option[<?php echo $field['key']; ?>]" id="<?php echo $optionKey; ?>" <?php checked( $option_value, $optionKey ); ?> value="<?php echo $optionKey; ?>" />
									<span class="icon-wrapper">
										<img src="<?php echo tutor()->url; ?>assets/images/images-v2/<?php echo $option['image']; ?>" alt="">
									</span>
									<span class="title"><?php echo $option['title']; ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
