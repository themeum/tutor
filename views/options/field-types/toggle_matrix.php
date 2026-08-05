<?php
/**
 * Toggle Matrix field type.
 * A generic matrix of toggles defined by $field['items'] and $field['columns'].
 * Each column in 'columns' has a 'label', 'icon', and optional 'icon_attrs' key.
 * Each item in 'items' must have a sub-array keyed by the column key with 'key' and 'default'.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 *
 * @since 4.0.5
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\SvgIcon;

/**
 * Field settings.
 *
 * @var array $field
 */
$items    = $field['items'] ?? array();
$columns  = $field['columns'] ?? array();
$field_id = 'field_' . $field['key'];
?>
<div class="tutor-option-field-row tutor-d-block tutor-toggle-matrix-field" id="<?php echo esc_attr( $field_id ); ?>">
	<div class="tutor-option-field-label">
		<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name><?php echo esc_html( $field['label'] ); ?></div>
		<div class="tutor-fs-7 tutor-color-muted"><?php echo esc_html( $field['desc'] ); ?></div>
	</div>

	<div class="tutor-toggle-matrix">
		<div class="tutor-toggle-matrix-head">
			<span></span>
			<?php foreach ( $columns as $column ) : ?>
				<span class="tutor-d-flex tutor-justify-center tutor-align-center tutor-gap-1 tutor-fs-6 tutor-fw-medium tutor-toggle-matrix-column">
					<?php if ( ! empty( $column['icon'] ) ) : ?>
						<span class="tutor-toggle-matrix-head-icon" aria-hidden="true">
							<?php
							$svg = SvgIcon::make()->name( $column['icon'] );
							foreach ( $column['icon_attrs'] ?? array() as $attr_name => $attr_value ) {
								$svg->attr( $attr_name, $attr_value );
							}
							$svg->render();
							?>
						</span>
					<?php endif; ?>
					<?php echo esc_html( $column['label'] ); ?>
				</span>
			<?php endforeach; ?>
		</div>

		<?php
		$total_items = count( $items );
		$item_index  = 0;
		foreach ( $items as $item ) :
			++$item_index;
			?>
			<div class="tutor-toggle-matrix-row">
				<div class="tutor-fs-6"><?php echo esc_html( $item['label'] ); ?></div>
				<?php foreach ( array_keys( $columns ) as $position ) : ?>
					<?php
						$setting      = $item[ $position ];
						$option_value = get_tutor_option( $setting['key'], $setting['default'] );
						$is_enabled   = 'on' === $option_value || true === $option_value || 1 === (int) $option_value;
					?>
					<label class="tutor-form-toggle tutor-toggle-matrix-toggle">
						<input
							type="hidden"
							name="tutor_option[<?php echo esc_attr( $setting['key'] ); ?>]"
							value="<?php echo esc_attr( $is_enabled ? 'on' : 'off' ); ?>"
						>
						<input
							type="checkbox"
							class="tutor-form-toggle-input"<?php checked( $is_enabled ); ?>
							aria-label="
								<?php
								echo esc_attr(
									sprintf(
										// translators: %1$s: item label, %2$s: position (header/footer).
										__( '%1$s %2$s', 'tutor' ),
										$item['label'],
										ucfirst( $position )
									)
								);
								?>
							"
						>
						<span class="tutor-form-toggle-control"></span>
					</label>
				<?php endforeach; ?>
			</div>
			<?php if ( $item_index < $total_items ) : ?>
				<div class="tutor-hr tutor-mx-12" aria-hidden="true" style="width: auto;"></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>
