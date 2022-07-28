<?php
/**
 * @package TutorLMS/Templates
 * @version 1.5.8
 */

?>

<?php tutor_course_loop_thumbnail(); ?>

<div class="tutor-course-bookmark">
    <?php
        $course_id = get_the_ID();
        $is_wish_listed = tutor_utils()->is_wishlisted( $course_id );
        
        $action_class = '';
        if ( is_user_logged_in() ) {
            $action_class = apply_filters('tutor_wishlist_btn_class', 'tutor-course-wishlist-btn');
        } else {
            $action_class = apply_filters('tutor_popup_login_class', 'tutor-open-login-modal');
        }
        
		echo '<a href="javascript:;" class="'. esc_attr( $action_class ) .' save-bookmark-btn tutor-iconic-btn tutor-iconic-btn-secondary" data-course-id="'. esc_attr( $course_id ) .'">
            <i class="' . ( $is_wish_listed ? 'tutor-icon-bookmark-bold' : 'tutor-icon-bookmark-line') . '"></i>
        </a>';
	?>
</div>
