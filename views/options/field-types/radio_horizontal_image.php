<?php
/**
 * Radio horizontal for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field settings.
 *
 * @var mixed $this  Options_V2 or Tools_V2 instance.
 * @var array $field
 */

$field_key = $field['key'] ?? '';
$field_id  = 'field_' . $field_key;
?>
<div class="tutor-option-field-row col-per-row" id="<?php echo esc_attr( $field_id ); ?>"
>
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="radio-thumbnail radio-thumbnail-image">
			<?php
			if ( ! empty( $field['options'] ) ) :
				foreach ( $field['options'] as $option_key => $option ) :
					$option_value = $this->get( $field_key, $field['default'] ?? '' );
					?>
					<label for="items-per-row-<?php echo esc_attr( $option_key ); ?>">
						<input type="radio" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" id="items-per-row-<?php echo esc_attr( $option_key ); ?>" <?php checked( $option_value, $option_key ); ?> value="<?php echo esc_attr( $option_key ); ?>" />
						<img src="<?php echo esc_url( tutor()->url . 'assets/images/images-v2/' . ( $option['image'] ?? '' ) ); ?>" alt="icon">
						<div class="image-title"><?php echo esc_html( $option['title'] ?? '' ); ?></div>
					</label>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
