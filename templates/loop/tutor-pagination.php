<?php
/**
 * A single course loop pagination
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php

	global $wp_query;

	

	function course_listing_pagination( $pages = '', $range = 4 ) {  
		$showitems = ( $range * 2 )+1;  
		global $paged;
		if(empty( $paged )) $paged = 1;
	 
		 if( $pages == '' ){
			$wp_query = '';
			$pages = $wp_query->max_num_pages;
			if( !$pages ){
				$pages = 1;
		}
	}   
	 
	if( 1 != $pages ){
	
?>
<nav class="tutor-course-list-pagination tutor-ui-pagination">
<div class="tutor-pagination-hints">
	<div class="text-regular-caption color-text-subsued">
		<?php _e('Page', 'tutor'); ?> 
		<span class="text-medium-caption color-text-primary">
		<?php echo esc_html( $paged ); ?> 
		</span>
		<?php _e('of', 'tutor'); ?>
		<span className="text-medium-caption color-text-primary">
		<?php echo esc_html( $pages ); ?>
		</span>
	</div>
</div>
<ul class="tutor-pagination-numbers">
	<?php
		if($paged > 1 ){
			echo wp_kses_post("<a href='".get_pagenum_link(1)."' class='prev page-numbers'><span class='ttr-angle-left-filled'></span></a>");
		}

		for ($i=1; $i <= $pages; $i++){
            if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )){
                echo ($paged == $i)? "<span class='page-numbers current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='page-numbers'>".$i."</a>";
            }
         }
 
        if ($paged < $pages) {
            echo wp_kses_post("<a href=\"".get_pagenum_link($paged + 1)."\" class='next page-numbers'><span class='ttr-angle-right-filled'></span></a>");
        }
	?>
</ul>
</nav>
<?php } }  ?>

<?php do_action('tutor_course/archive/pagination/before');  ?>
	
	<?php 
		if (function_exists("course_listing_pagination")) {
		course_listing_pagination( $wp_query->max_num_pages );
		} 
	?>

<?php do_action('tutor_course/archive/pagination/after');  ?>
