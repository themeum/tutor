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

<div class="tutor-option-field-row tutor-d-block" id="<?php echo esc_attr( $field_id ); ?>"
>
<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="radio-thumbnail horizontal">
			<?php
			if ( ! empty( $field['options'] ) ) :
				foreach ( $field['options'] as $option ) :
					$option_slug  = $option['slug'] ?? '';
					$option_thumb = $option['thumb_url'] ?? '';
					$option_title = $option['title'] ?? '';
					?>
					<label for="<?php echo esc_attr( $option_slug ); ?>">
						<input type="radio" name="certificate-template" id="<?php echo esc_attr( $option_slug ); ?>">
						<span class="icon-wrapper">
							<img src="<?php echo esc_url( tutor()->url . 'assets/images/images-v2/' . $option_thumb ); ?>" alt="<?php echo esc_attr( $option_title ); ?>">
						</span>
					</label>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
