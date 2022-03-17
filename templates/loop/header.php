<?php
/**
 * @package TutorLMS/Templates
 * @version 1.5.8
 */

?>

<div class="tutor-course-listing-item-head tutor-d-flex">
	<?php
	    tutor_course_loop_thumbnail();

	    $course_id = get_the_ID();
	?>
    <?php
        $is_wishlisted = tutor_utils()->is_wishlisted($course_id);
        
        $action_class = '';
        if ( is_user_logged_in()){
            $action_class = apply_filters('tutor_wishlist_btn_class', 'tutor-course-wishlist-btn');
        }else{
            $action_class = apply_filters('tutor_popup_login_class', 'cart-required-login');
        }
        
		echo '<a href="javascript:;" class="'. esc_attr( $action_class ) .' save-bookmark-btn tutor-d-flex tutor-align-items-center tutor-justify-content-center" data-course-id="'. esc_attr( $course_id ) .'">
            <i class="'.($is_wishlisted ? 'tutor-icon-fav-full-filled' : 'tutor-icon-fav-line-filled').'"></i>
        </a>';
	?>
</div>
