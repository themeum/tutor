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
		<?php
		/* $check_allowed = array('email_to_students');
		if (isset($blocks['slug']) && in_array($blocks['slug'], $check_allowed)) {
		?>
			<div style="display: flex;justify-content: end;">
				<label><input type="checkbox" class="check_block_checkbox"> Check all</label>
				<script>
					setTimeout(() => {
						let checkAllElem = document.querySelector('.check_block_checkbox');
						let allCheckedElem = document.querySelectorAll('#email_notification input[type=checkbox].tutor-form-toggle-input');
						checkAllElem.onchange = (item) => {
							if (true === checkAllElem.checked) {
								allCheckedElem.forEach((item) => {
									item.change
									item.checked = true;
									item.setAttribute('value', 'on');
									console.log(item.previousElementSibling);
									item.previousElementSibling.setAttribute('value', 'on');
								})
							} else {
								allCheckedElem.forEach((item) => {
									item.checked = false;
									item.removeAttribute("checked");
									item.setAttribute('value', 'off');
									item.previousElementSibling.setAttribute('value', 'off');
								})
							}
						}
					})
				</script>
			</div>
		<?php } */
		?>
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
		<div class="tutor-option-group-title tutor-d-flex tutor-align-items-center tutor-mb-16">
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