<?php

/**
 * Option block for tutor options.
 *
 * @package Tutor LMS
 * @since 2.0
 */
// @todo: replace the h4
?>
<?php if ($blocks['block_type'] == 'uniform') : ?>
	<div class="tutor-option-single-item tutor-mb-32 <?php echo isset($blocks['class']) ? esc_attr($blocks['class']) : (isset($blocks['slug']) ? esc_attr($blocks['slug']) : null); ?>">
		<?php if ( isset($blocks['label']) ) : ?>
			<div class="tutor-option-group-title tutor-mb-16">
				<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_attr($blocks['label']); ?></div>
			</div>
		<?php endif; ?>
		<div class="item-wrapper">
			<?php
			foreach ($blocks['fields'] as $field) :
				$this->generate_field($field);
			endforeach;
			?>
		</div>
	</div>

<?php elseif ( $blocks['block_type'] == 'isolate' ) : ?>

	<div class="tutor-option-single-item tutor-mb-32 <?php echo $blocks['slug']; ?>">
		<?php if ( isset($blocks['label']) ) : ?>
			<div class="tutor-option-group-title tutor-mb-16">
				<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_attr($blocks['label']); ?></div>
			</div>
		<?php endif; ?>
		<?php foreach ( $blocks['fields'] as $field ) : ?>
			<div class="item-wrapper">
				<?php echo $this->generate_field( $field ); ?>
			</div>
		<?php endforeach; ?>
	</div>

<?php elseif ($blocks['block_type'] == 'notification') : ?>

	<div class="tutor-option-single-item tutor-mb-32">
		<div class="tutor-option-group-title tutor-d-flex tutor-align-center tutor-mb-16">
			<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_attr($blocks['label']); ?></div>
			<div class="tutor-fs-6 tutor-color-muted tutor-ml-auto tutor-mr-lg-32"><?php echo esc_attr($blocks['status_label']); ?></div>
		</div>

		<div class="item-wrapper">
			<?php
			foreach ($blocks['fields'] as $field) :
				echo $this->generate_field($field);
			endforeach;
			?>
		</div>
	</div>

<?php elseif ($blocks['block_type'] == 'column') : ?>
	<div class="tutor-option-single-item tutor-mb-32 item-variation-grid <?php echo esc_attr($blocks['slug']); ?>">
		<!-- @todo: know the use -->
		<?php if ( isset($blocks['label']) ) : ?>
			<div class="tutor-option-group-title tutor-mb-16">
				<div class="tutor-fs-6 tutor-color-muted"><?php echo esc_attr($blocks['label']); ?></div>
			</div>
		<?php endif; ?>
		<div class="item-grid">
			<?php foreach ($blocks['fieldset'] as $fieldset) : ?>
				<div class="item-wrapper">
					<?php foreach ($fieldset as $field) : ?>
						<?php echo $this->generate_field($field); ?>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

<?php
elseif ($blocks['block_type'] == 'color_picker') :
	echo $this->template(
		array(
			'template' => $blocks['block_type'],
			'blocks'   => $blocks,
		)
	);
elseif ($blocks['block_type'] == 'custom') :
	include $blocks['template_path'];
endif;
?>