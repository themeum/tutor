<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

?>

<div class="tutor-course-header">
	<?php
	tutor_course_loop_thumbnail();

	$course_id = get_the_ID();
	?>
    <div class="tutor-course-loop-header-meta">
		<?php
        $is_wishlisted = tutor_utils()->is_wishlisted($course_id);
        $has_wish_list = '';
        if ($is_wishlisted){
	        $has_wish_list = 'has-wish-listed';
        }

		echo '<span class="tutor-course-loop-level">'.get_tutor_course_level().'</span>';
		echo '<span class="tutor-course-wishlist"><a href="javascript:;" class="tutor-icon-fav-line tutor-course-wishlist-btn '.$has_wish_list.' " data-course-id="'.$course_id.'"></a> </span>';
		?>
    </div>
</div>