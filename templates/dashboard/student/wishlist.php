<?php global $post; ?>


<h1><?php _e('WishList', 'dozent'); ?></h1>
<div class="dozent-dashboard-content-inner">

	<?php
	$wishlists = dozent_utils()->get_wishlist();


	if (is_array($wishlists) && count($wishlists)):
        foreach ($wishlists as $post):
	        setup_postdata($post);

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

		endforeach;

		wp_reset_postdata();

	else:
        echo "There's no active course";
	endif;

	?>
</div>
