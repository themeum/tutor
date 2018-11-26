<form method="post" enctype="multipart/form-data">
	<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
	<input type="hidden" value="tutor_apply_teacher" name="tutor_action"/>

	<div class="tutor-form-row">
		<div class="tutor-form-col-12">
			<div class="tutor-form-group">
				<button type="submit" name="tutor_register_teacher_btn" value="apply"><?php _e('Apply for become teacher', 'tutor'); ?></button>
			</div>
		</div>
	</div>

</form>