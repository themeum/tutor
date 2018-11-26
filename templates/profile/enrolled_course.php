<?php
$user_name = sanitize_text_field(get_query_var('dozent_student_username'));
$get_user = dozent_utils()->get_user_by_login($user_name);
$user_id = $get_user->ID;

$my_courses = dozent_utils()->get_enrolled_courses_by_user($user_id);
?>

<div class="dozent-courses <?php dozent_container_classes() ?>">
	<?php
	if ($my_courses && $my_courses->have_posts()):
		while ($my_courses->have_posts()):
			$my_courses->the_post();
			/**
			 * @hook dozent_course/archive/before_loop_course
			 * @type action
			 * Usage Idea, you may keep a loop within a wrap, such as bootstrap col
			 */
			do_action('dozent_course/archive/before_loop_course');

			dozent_load_template('loop.course');

			/**
			 * @hook dozent_course/archive/after_loop_course
			 * @type action
			 * Usage Idea, If you start any div before course loop, you can end it here, such as </div>
			 */
			do_action('dozent_course/archive/after_loop_course');

		endwhile;
		wp_reset_postdata();
	else : ?>
    <div>
        <h2><?php _e("Not Found" , 'dozent'); ?></h2>
        <p><?php _e("Sorry, but you are looking for something that isn't here." , 'dozent'); ?></p>
    </div>
	<?php endif; ?>
</div>

