<?php
$is_teacher = tutor_utils()->is_teacher();
if ($is_teacher){
	?>

    <div class="tutor-alert-warning">
        <h2><?php _e("You are teacher", 'tutor'); ?></h2>

        <p>
			<?php _e(sprintf("Registered at : %s %s", date_i18n(get_option('date_format'), $is_teacher), date_i18n(get_option('time_format'), $is_teacher) ), 'tutor'); ?>
        </p>

        <p>
			<?php _e(sprintf('Status : %s', tutor_utils()->teacher_status()), 'tutor'); ?>
        </p>

    </div>

<?php }else{
    tutor_load_template('dashboard.teacher.apply_for_teacher');
} ?>