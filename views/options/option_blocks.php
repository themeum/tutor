<?php
/**
 * Option block for tutor options.
 *
 * @package Tutor\Views
 * @subpackage Tutor\Options
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

// @todo: replace the h4
$block_slug = $blocks['slug'] ?? '';
?>
<?php if ( 'uniform' == $blocks['block_type'] ) : ?>
	<div class="tutor-option-single-item tutor-mb-32 <?php echo isset( $blocks['class'] ) ? esc_attr( $blocks['class'] ) : ( isset( $blocks['slug'] ) ? esc_attr( $blocks['slug'] ) : null ); ?>">
		<?php if ( isset( $blocks['label'] ) ) : ?>
			<div class="tutor-option-group-title tutor-mb-16">
				<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_attr( $blocks['label'] ); ?></div>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $blocks['fields'] ) ) : ?>
			<div class="item-wrapper">
				<?php
				foreach ( $blocks['fields'] as $field ) :
					$this->generate_field( $field );
				endforeach;
				?>
			</div>
		<?php endif; ?>
		<?php do_action( 'tutor_after_block_single_item', $block_slug ); ?>
	</div>
<?php elseif ( 'manual_payment' === $blocks['block_type'] ) : ?>
	<div class="tutor-option-single-item tutor-mb-32 <?php echo isset( $blocks['class'] ) ? esc_attr( $blocks['class'] ) : ( isset( $blocks['slug'] ) ? esc_attr( $blocks['slug'] ) : null ); ?>">
		<?php if ( isset( $blocks['label'] ) ) : ?>
			<div class="tutor-option-group-title tutor-mb-16">
				<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_attr( $blocks['label'] ); ?></div>
			</div>
		<?php endif; ?>
		<div class="item-wrapper">
			<div class="tutor-option-field-row">
				<div class="tutor-option-field-label">
					<?php isset( $blocks['label'] ) ? printf( '<div class="tutor-fs-6 tutor-fw-medium tutor-mb-8" tutor-option-name>%s</div>', esc_attr( $blocks['label'] ) ) : null; ?>
					<?php isset( $blocks['desc'] ) ? printf( '<div class="tutor-fs-7 tutor-color-muted">%s</div>', wp_kses_post( $blocks['desc'] ) ) : null; ?>
				</div>

				<div class="tutor-option-field-input tutor-d-flex tutor-gap-1">
					<label class="tutor-form-toggle">
						<input type="checkbox" 
							<?php checked( esc_attr( $blocks['default'] ), 'on' ); ?> 
							class="tutor-form-toggle-input" data-payment-method-id="<?php echo esc_attr( $blocks['payment_method_id'] ); ?>">
						<span class="tutor-form-toggle-control"></span>
					</label>
					<div class="tutor-dropdown-parent">
						<button type="button" class="tutor-iconic-btn" action-tutor-dropdown="toggle">
							<span class="tutor-icon-kebab-menu" area-hidden="true"></span>
						</button>
						<div class="tutor-dropdown tutor-dropdown-dark tutor-text-left">
							<a class="tutor-dropdown-item" href="javascript:void(0)" 
							<?php
							if ( is_array( $blocks['data-attrs'] ) && count( $blocks['data-attrs'] ) ) {
								foreach ( $blocks['data-attrs'] as $k => $attr ) {
									echo wp_kses_post( "data-{$k}" . '="' . $attr . '"' );
								}
							}
							?>
							>
								<i class="tutor-icon-edit tutor-mr-8" area-hidden="true"></i>
								<span><?php esc_html_e( 'Edit', 'tutor' ); ?></span>
							</a>
							<a href="javascript:void(0)" class="tutor-dropdown-item tutor-manual-payment-method-delete" data-payment-method-id="<?php echo esc_attr( $blocks['payment_method_id'] ); ?>">
								<i class="tutor-icon-trash-can-bold tutor-mr-8" area-hidden="true"></i>
								<span><?php esc_html_e( 'Delete', 'tutor' ); ?></span>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php elseif ( 'isolate' == $blocks['block_type'] ) : ?>
	<div class="tutor-option-single-item tutor-mb-32 <?php echo esc_attr( $blocks['slug'] ); ?>">
		<?php if ( isset( $blocks['label'] ) ) : ?>
			<div class="tutor-option-group-title tutor-mb-16">
				<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_attr( $blocks['label'] ); ?></div>
			</div>
		<?php endif; ?>
		<?php foreach ( $blocks['fields'] as $field ) : ?>
			<div class="item-wrapper">
				<?php echo $this->generate_field( $field ); //phpcs:ignore -- contain safe data ?>
			</div>
		<?php endforeach; ?>
	</div>

<?php elseif ( 'notification' == $blocks['block_type'] ) : ?>

	<div class="tutor-option-single-item tutor-mb-32">
		<div class="tutor-option-group-title tutor-d-flex tutor-align-center tutor-mb-16">
			<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_attr( $blocks['label'] ); ?></div>
			<div class="tutor-fs-6 tutor-color-muted tutor-ml-auto tutor-mr-lg-32"><?php echo esc_attr( $blocks['status_label'] ); ?></div>
		</div>

		<div class="item-wrapper">
			<?php
			foreach ( $blocks['fields'] as $field ) :
				echo $this->generate_field( $field ); //phpcs:ignore -- contain safe data
			endforeach;
			?>
		</div>
	</div>

<?php elseif ( 'column' == $blocks['block_type'] ) : ?>
	<div class="tutor-option-single-item tutor-mb-32 item-variation-grid <?php echo esc_attr( $blocks['slug'] ); ?>">
		<!-- @todo: know the use -->
		<?php if ( isset( $blocks['label'] ) ) : ?>
			<div class="tutor-option-group-title tutor-mb-16">
				<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_attr( $blocks['label'] ); ?></div>
			</div>
		<?php endif; ?>
		<div class="item-grid">
			<?php foreach ( $blocks['fieldset'] as $fieldset ) : ?>
				<div class="item-wrapper">
					<?php foreach ( $fieldset as $field ) : ?>
						<?php echo $this->generate_field( $field ); //phpcs:ignore -- contain safe data ?>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<?php
elseif ( 'color_picker' == $blocks['block_type'] ) :
	//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	// Data already escaped.
	echo $this->template(
		array(
			'template' => $blocks['block_type'],
			'blocks'   => $blocks,
		)
	);
elseif ( 'custom' == $blocks['block_type'] ) :
	include $blocks['template_path'];
elseif ( 'action_placeholder' === $blocks['block_type'] ) :
	do_action( $blocks['action'] );
endif;
?>
