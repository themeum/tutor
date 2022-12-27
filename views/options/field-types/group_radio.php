<?php
/**
 * Group radio for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$field_key = isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;

$field_id = esc_attr( 'field_' . $field_key );
?>
<div class="tutor-option-field-row tutor-d-block">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input" id="<?php echo esc_attr( $field_id ); ?>">
		<div class="radio-thumbnail has-title instructor-list">
			<?php
			if ( ! empty( $field['group_options'] ) ) :
				foreach ( $field['group_options'] as $group_option_key => $options ) :
					?>
					<div class="<?php echo esc_attr( $group_option_key ); ?>">
						<div class="fields-wrapper">
							<?php
							foreach ( $options as $option_key => $option ) :
								$option_value = $this->get( $field['key'], tutils()->array_get( 'default', $field ) );
								?>
								<label for="<?php echo esc_attr( $option_key ); ?>">
									<input type="radio" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" id="<?php echo esc_attr( $option_key ); ?>" <?php echo esc_attr( checked( $option_value, $option_key ) ); ?> value="<?php echo esc_attr( $option_key ); ?>" />
									<span class="icon-wrapper">
										<img src="<?php echo esc_url( tutor()->url . 'assets/images/images-v2/' . $option['image'] ); ?>" alt="icon">
									</span>
									<span class="title"><?php echo esc_attr( $option ['title'] ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
