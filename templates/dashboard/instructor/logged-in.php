<?php
$is_instructor = tutor_utils()->is_instructor();
if ($is_instructor){
	?>

    <div class="tutor-alert-warning tutor-instructor-alert">
        <h2><?php _e("You are instructor", 'tutor'); ?></h2>

        <p>
			<?php _e(sprintf("Registered at : %s %s", date_i18n(get_option('date_format'), $is_instructor), date_i18n(get_option('time_format'), $is_instructor) ), 'tutor'); ?>
        </p>

        <p>
			<?php _e(sprintf('Status : %s', tutor_utils()->instructor_status()), 'tutor'); ?>
        </p>

    </div>

<?php }else{
    tutor_load_template('dashboard.instructor.apply_for_instructor');
} ?>