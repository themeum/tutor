<?php
/**
 * Radio thumbnail grid for settings.
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

<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>"
>
<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="radio-thumbnail horizontal">
			<?php
			$i = 1;
			foreach ( $field['options'] as $key => $option ) :
				?>
				<label for="<?php echo esc_attr( $option['slug'] ); ?>">
					<input type="radio" name="certificate-template" id="<?php echo esc_attr( $option['slug'] ); ?>">
					<span class="icon-wrapper">
						<img src="<?php echo esc_url( tutor()->url . 'assets/images/images-v2/' . esc_attr( $option['thumb_url'] ) ); ?>" alt="<?php echo esc_attr( $option['title'] ); ?>">
					</span>
				</label>
			<?php endforeach; ?>

		</div>
	</div>
</div>
