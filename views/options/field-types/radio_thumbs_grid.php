<?php $field_id = 'field_' . $field['key']; ?>

<div class="tutor-option-field-row d-block" id="<?php echo $field_id; ?>"
>
<?php require tutor()->path . 'views/options/template/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<div class="radio-thumbnail horizontal">
			<?php
			$i = 1;
			foreach ( $field['options'] as $key => $option ) :
				?>
				<label for="<?php echo $option['slug']; ?>">
					<input type="radio" name="certificate-template" id="<?php echo $option['slug']; ?>">
					<span class="icon-wrapper">
						<img src="<?php echo tutor()->url; ?>assets/images/images-v2/<?php echo $option['thumb_url']; ?>" alt="<?php echo $option['title']; ?>">
					</span>
				</label>
			<?php endforeach; ?>

		</div>
	</div>
</div>
