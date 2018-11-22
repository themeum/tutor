<?php
$is_teacher = dozent_utils()->is_teacher();
if ($is_teacher){
	?>

    <div class="dozent-alert-warning">
        <h2><?php _e("You are teacher", 'dozent'); ?></h2>

        <p>
			<?php _e(sprintf("Registered at : %s %s", date_i18n(get_option('date_format'), $is_teacher), date_i18n(get_option('time_format'), $is_teacher) ), 'dozent'); ?>
        </p>

        <p>
			<?php _e(sprintf('Status : %s', dozent_utils()->teacher_status()), 'dozent'); ?>
        </p>

    </div>

<?php }else{
    dozent_load_template('dashboard.teacher.apply_for_teacher');
} ?>