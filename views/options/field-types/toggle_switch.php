<div class="tutor-option-field-row">
	<div class="tutor-option-field-label">
		<label><?php echo $field['label']; ?></label>
		<p class="desc"><?php echo $field['desc'] ?></p>
	</div>
	<div class="tutor-option-field-input">
		<label class="tutor-form-toggle">
			<?php echo $field['label_title'] !== null ? "<span class='label-before'>{$field['label_title']}</span>" : null; ?>
			<input type="checkbox" class="tutor-form-toggle-input" />
			<span class="tutor-form-toggle-control"></span>
		</label>
	</div>
</div>