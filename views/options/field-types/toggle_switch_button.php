<?php
/**
 * Toggle switch button for email settings page.
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

$field_default   = $field['default'] ?? 'off';
$field_key       = $field['key'] ?? '';
$field_event     = $field['event'] ?? '';
$field_label     = $field['label'] ?? '';
$option_value    = $this->get( $field_key . '.' . $field_event, $field_default );
$field_key_event = sanitize_key( $field_key . '_' . $field_event );
$field_id        = 'field_' . $field_key_event;
$tooltip_desc    = ! empty( $field['tooltip'] ) ? $field['tooltip'] : '';
$editable        = $field['editable'] ?? true;
?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="tutor-option-field-label <?php echo $tooltip_desc ? 'has-tooltip' : ''; ?>">
		<div class="tutor-fs-6 tutor-fw-medium tutor-my-4" tutor-option-name>
			<?php echo esc_html( $field_label ); ?>
		</div>
		<?php if ( $tooltip_desc ) { ?>
			<div class="tooltip-wrap tooltip-icon">
				<span class="tooltip-txt tooltip-top"><?php echo esc_html( $tooltip_desc ); ?></span>
			</div>
			<span style="white-space: nowrap;"></span>
		<?php } ?>
	</div>
	<div class="tutor-option-field-input tutor-d-flex">
	<?php
	if ( isset( $field['buttons'] ) ) {
		$total_btn = count( $field['buttons'] );

		echo $total_btn ? '<div class="tutor-mr-16">' : '';
		foreach ( $field['buttons'] as $button ) {
			if ( 'anchor' === ( $button['type'] ?? '' ) ) {
				?>
					<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" href="<?php echo esc_url( $button['url'] ?? '' ); ?>"><?php echo esc_html( $button['text'] ?? '' ); ?></a>
					<?php
			}
		}
		echo $total_btn ? '</div>' : '';
	}

	if ( $editable ) {
		?>
	<label class="tutor-form-toggle">
		<input type="hidden" name="tutor_option[<?php echo esc_attr( $field_key ); ?>][<?php echo esc_attr( $field_event ); ?>]" value="<?php echo esc_attr( $option_value ); ?>">
		<input type="checkbox" value="on" <?php checked( $option_value, 'on' ); ?> class="tutor-form-toggle-input">
		<span class="tutor-form-toggle-control"></span>
	</label>
	<?php } ?>
	</div>
</div>
