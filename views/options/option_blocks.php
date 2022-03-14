<?php

/**
 * Option block for tutor options.
 *
 * @package Tutor LMS
 * @since 2.0
 */
// pr($blocks);
?>
<?php if ($blocks['block_type'] == 'uniform') : ?>
	<div class="tutor-option-single-item <?php echo isset($blocks['class']) ? esc_attr($blocks['class']) : (isset($blocks['slug']) ? esc_attr($blocks['slug']) : null); ?>">
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
		<?php echo isset($blocks['label']) ? '<h4>' . esc_attr($blocks['label']) . '</h4>' : ''; ?>
		<div class="item-wrapper">
			<?php
			foreach ($blocks['fields'] as $field) :
				$this->generate_field($field);
			endforeach;
			?>
		</div>
	</div>

<?php elseif ( $blocks['block_type'] == 'isolate' ) : ?>

	<div class="tutor-option-single-item <?php echo $blocks['slug']; ?>">
		<?php echo $blocks['label'] ? '<h4>' . $blocks['label'] . '</h4>' : ''; ?>
		<?php foreach ( $blocks['fields'] as $field ) : ?>
			<div class="item-wrapper">
				<?php echo $this->generate_field( $field ); ?>
			</div>
		<?php endforeach; ?>
	</div>

<?php elseif ($blocks['block_type'] == 'notification') : ?>

	<div class="tutor-option-single-item">
		<div class="item-title">
			<div class="tutor-d-flex">
				<h4><?php echo esc_attr($blocks['label']); ?></h4>
				<div class="tooltip-wrap tooltip-icon">
					<span class="tooltip-txt tooltip-right"><?php echo esc_attr($blocks['tooltip']); ?></span>
				</div>
			</div>
			<h4><?php echo esc_attr($blocks['status_label']); ?></h4>
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

	<div class="tutor-option-single-item item-variation-grid <?php echo esc_attr($blocks['slug']); ?>">
		<?php echo isset($blocks['label']) ? '<h4>' . esc_attr($blocks['label']) . '</h4>' : ''; ?>
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