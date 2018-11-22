<div class="dozent-option-field-row">
	<?php
	if (isset($field['label'])){
		?>
        <div class="dozent-option-field-label">
            <label for=""><?php echo $field['label']; ?></label>
        </div>
		<?php
	}
	?>
    <div class="dozent-option-field">
		<?php
        echo $this->field_type($field);

		if (isset($field['desc'])){
			echo "<p class='desc'>{$field['desc']}</p>";
		}
		?>
    </div>
</div>