<?php
/**
 * Radio horizontal for settings.
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
<div class="tutor-option-field-row col-1x2 col-per-row" id="<?php echo esc_attr( $field_id ); ?>"
>
	<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="tutor-d-flex radio-thumbnail items-per-row">
			<?php
			$i = 1;
			if ( ! empty( $field['options'] ) ) :
				foreach ( $field['options'] as $option_key => $option ) :
					$option_value = $this->get( $field_key, $field['default'] ?? '' );
					?>
					<label for="items-per-row-<?php echo esc_attr( $option_key ); ?>" class="items-per-row-label">
						<input type="radio" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" id="items-per-row-<?php echo esc_attr( $option_key ); ?>" <?php checked( $option_value, $option_key ); ?> value="<?php echo esc_attr( $option_key ); ?>" />
						<span class="icon-wrapper icon-col">
							<?php echo str_repeat( '<span></span>', $i++ ); //phpcs:ignore ?>
						</span>
						<span class="title"><?php echo esc_html( $option ); ?></span>
					</label>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
