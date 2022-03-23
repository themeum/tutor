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
			$pages = is_object( $wp_query ) ? $wp_query->max_num_pages : null;
			if( !$pages ){
				$pages = 1;
		}
	}

	if( 1 != $pages ){

?>
<nav class="tutor-course-list-pagination tutor-ui-pagination">
	<div class="tutor-pagination-hints">
		<div class="tutor-fs-7 tutor-color-black-60">
			<?php _e('Page', 'tutor'); ?>
			<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
			<?php echo esc_html( $paged ); ?>
			</span>
			<?php _e('of', 'tutor'); ?>
			<span className="tutor-fs-7 tutor-fw-medium tutor-color-black">
			<?php echo esc_html( $pages ); ?>
			</span>
		</div>
	</div>
	<ul class="tutor-pagination-numbers">
		<?php
			if($paged > 1 ){
				echo "<a href='".esc_url( get_pagenum_link(1) )."' class='prev page-numbers'>
						<span class='tutor-icon-angle-left-filled'></span>
					</a>";
			}

			for ($i=1; $i <= $pages; $i++){
				if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )){
					echo ($paged == $i)? "<span class='page-numbers current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='page-numbers'>".$i."</a>";
				}
			}

			if ($paged < $pages) {
				echo '<a href="'.esc_url( get_pagenum_link($paged + 1) ).'" class="next page-numbers">
						<span class="tutor-icon-angle-right-filled"></span>
					</a>';
			}
		?>
	</ul>
</nav>
<?php } }  ?>

<?php do_action('tutor_course/archive/pagination/before');  ?>

	<?php
		if (function_exists("course_listing_pagination") && is_object($wp_query)) {
			course_listing_pagination( $wp_query->max_num_pages );
		}
	?>

<?php do_action('tutor_course/archive/pagination/after');  ?>
