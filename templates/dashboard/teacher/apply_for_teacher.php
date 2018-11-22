<form method="post" enctype="multipart/form-data">
	<?php wp_nonce_field( dozent()->nonce_action, dozent()->nonce ); ?>
	<input type="hidden" value="dozent_apply_teacher" name="dozent_action"/>

	<div class="dozent-form-row">
		<div class="dozent-form-col-12">
			<div class="dozent-form-group">
				<button type="submit" name="dozent_register_teacher_btn" value="apply"><?php _e('Apply for become teacher', 'dozent'); ?></button>
			</div>
		</div>
	</div>

</form>