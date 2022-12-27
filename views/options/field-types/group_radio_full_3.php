<?php
/**
 * Full group radio for settings.
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
<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="radio-thumbnail has-title public-profile fields-wrapper">
			<?php if ( ! empty( $field['group_options'] ) ) : ?>
				<?php
				foreach ( $field['group_options'] as $option_key => $option ) :
					$option_value = $this->get( $field['key'], tutils()->array_get( 'default', $field ) );
					?>
					<label>
						<input type="radio" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" <?php echo esc_attr( checked( $option_value, $option_key ) ); ?> value="<?php echo esc_attr( $option_key ); ?>">
						<span class="icon-wrapper">
							<img src="<?php echo esc_url( tutor()->url . 'assets/images/images-v2/' . $option['image'] ); ?>" alt="icon">
						</span>
						<span class="title"><?php echo esc_attr( $option ['title'] ); ?></span>
					</label>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
