<?php
/**
 * Page Elements settings matrix.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Settings
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Icon;
use Tutor\Components\SvgIcon;

$elements = array(
	'dashboard' => array(
		'label'  => __( 'Dashboard', 'tutor' ),
		'header' => array(
			'key'     => 'show_dashboard_site_header',
			'default' => 'on',
		),
		'footer' => array(
			'key'     => 'show_dashboard_site_footer',
			'default' => 'on',
		),
	),
	'learning'  => array(
		'label'  => __( 'Learning Experience', 'tutor' ),
		'header' => array(
			'key'     => 'show_learning_site_header',
			'default' => 'off',
		),
		'footer' => array(
			'key'     => 'show_learning_site_footer',
			'default' => 'off',
		),
	),
);
?>
<div class="tutor-option-field-row tutor-d-block tutor-page-elements-field" id="field_page_elements">
	<div class="tutor-option-field-label">
		<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name><?php echo esc_html( $field['label'] ); ?></div>
		<div class="tutor-fs-7 tutor-color-muted"><?php echo esc_html( $field['desc'] ); ?></div>
	</div>

	<div class="tutor-page-elements-matrix">
		<div class="tutor-page-elements-matrix-head">
			<span></span>
			<span class="tutor-d-flex tutor-justify-center tutor-align-center tutor-gap-1 tutor-fs-6 tutor-fw-medium">
				<span class="tutor-page-elements-head-icon" aria-hidden="true">
					<?php SvgIcon::make()->name( Icon::FOOTER )->render(); ?>
				</span>
				<?php esc_html_e( 'Header', 'tutor' ); ?>
			</span>
			<span class="tutor-page-elements-matrix-column">
				<span class="tutor-page-elements-head-icon" aria-hidden="true">
					<?php SvgIcon::make()->name( Icon::FOOTER )->render(); ?>
				</span>
				<?php esc_html_e( 'Footer', 'tutor' ); ?>
			</span>
		</div>

		<?php
		$total_elements = count( $elements );
		$element_index  = 0;
		foreach ( $elements as $element ) :
			++$element_index;
			?>
			<div class="tutor-page-elements-matrix-row">
				<div class="tutor-fs-6"><?php echo esc_html( $element['label'] ); ?></div>
				<?php foreach ( array( 'header', 'footer' ) as $position ) : ?>
					<?php
					$setting      = $element[ $position ];
					$option_value = $this->get( $setting['key'], $setting['default'] );
					$is_enabled   = 'on' === $option_value || true === $option_value || 1 === (int) $option_value;
					?>
					<label class="tutor-form-toggle tutor-page-elements-toggle">
						<input type="hidden" name="tutor_option[<?php echo esc_attr( $setting['key'] ); ?>]" value="<?php echo esc_attr( $is_enabled ? 'on' : 'off' ); ?>">
						<input type="checkbox" class="tutor-form-toggle-input"<?php checked( $is_enabled ); ?> aria-label="
						<?php
						echo esc_attr(
							sprintf(
							// translators: %1$s: element label, %2$s: position (header/footer).
								__( '%1$s %2$s', 'tutor' ),
								$element['label'],
								ucfirst( $position )
							)
						);
						?>
							">
						<span class="tutor-form-toggle-control"></span>
					</label>
				<?php endforeach; ?>
			</div>
			<?php if ( $element_index < $total_elements ) : ?>
				<div class="tutor-hr tutor-mx-12" aria-hidden="true" style="width: auto;"></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>
