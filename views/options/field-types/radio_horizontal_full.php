<?php
/**
 * Radio horizontal full for settings.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
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
<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>">
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="type-check tutor-d-flex">
			<?php
			if ( ! empty( $field['options'] ) ) :
				foreach ( $field['options'] as $option_key => $option ) :
					$option_value = $this->get( $field_key, $field['default'] ?? '' );
					?>
					<div class="tutor-form-check">
						<input id="radio_<?php echo esc_attr( $option_key ); ?>" type="radio" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $option_key ); ?>" <?php checked( $option_value, $option_key ); ?> class="tutor-form-check-input" />
						<label for="radio_<?php echo esc_attr( $option_key ); ?>"><?php echo esc_html( $option ); ?></label>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
