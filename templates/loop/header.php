<div class="dozent-course-header">
	<?php
	dozent_course_loop_thumbnail();

	$course_id = get_the_ID();
	?>
    <div class="dozent-course-loop-header-meta">
		<?php
        $is_wishlisted = dozent_utils()->is_wishlisted($course_id);
        $has_wish_list = '';
        if ($is_wishlisted){
	        $has_wish_list = 'has-wish-listed';
        }

		echo '<span class="dozent-course-loop-level">'.get_dozent_course_level().'</span>';
		echo '<span class="dozent-course-wishlist"><a href="javascript:;" class="dozent-icon-fav-line dozent-course-wishlist-btn '.$has_wish_list.' " data-course-id="'.$course_id.'"></a> </span>';
		?>
    </div>
</div>