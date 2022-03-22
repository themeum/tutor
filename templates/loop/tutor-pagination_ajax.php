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
	$showitems = ( $range * 2 ) + 1;
	global $paged;
	$paged = $_POST['page'];
	if ( empty( $paged ) ) {
		$paged = 1;
	}

	if ( $pages == '' ) {
		/* $wp_query = '';
		$pages    = is_object( $wp_query ) ? $wp_query->max_num_pages : null; */
		$pages = $_POST['page'];
		if ( ! $pages ) {
			$pages = 1;
		}
	}

	if ( 1 != $pages ) {
		?>
<nav class="tutor-course-list-pagination tutor-ui-pagination">
	<div class="tutor-pagination-hints">
		<div class="tutor-fs-7 tutor-color-black-60">
		<?php _e( 'Page', 'tutor' ); ?>
			<span class="tutor-fs-7 tutor-fw-medium tutor-color-black">
		<?php echo esc_html( $paged ); ?>
			</span>
		<?php _e( 'of', 'tutor' ); ?>
			<span className="tutor-fs-7 tutor-fw-medium tutor-color-black">
		<?php echo esc_html( $pages ); ?>
			</span>
		</div>
	</div>
	<?php //echo $paged; ?>
	<ul class="tutor-pagination-numbers course-archive-page">
		<?php

		if ( $paged > 1 ) {
			echo "<a data-pagenumber='" .( $paged - 1 )."' href='javascript:void(0)' class='prev page-numbers'>

					<span class='tutor-icon-angle-left-filled'></span>
				</a>";
		}

		for ( $i = 1; $i <= $pages; $i++ ) {
			if ( 1 != $pages && ( ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) || $pages <= $showitems ) ) {
				echo ( $paged == $i ) ? "<span class='page-numbers current'>" . $i . '</span>' : "<a data-pagenumber='$i' href='javascript:void(0)' class='page-numbers'>" . $i . '</a>';
			}
		}

		if ( $paged < $pages ) {
			echo '<a data-pagenumber="' . ($paged + 1).'" href="javascript:void(0)" class="next page-numbers">
					<span class="tutor-icon-angle-right-filled"></span>
				</a>';
		}
		?>
	</ul>
</nav>
	<?php } } ?>

<?php do_action( 'tutor_course/archive/pagination/before' ); ?>

	<?php
	if ( function_exists( 'course_listing_pagination' ) && is_object( $wp_query ) ) {
		course_listing_pagination( $wp_query->max_num_pages );
	}
	?>

<?php do_action( 'tutor_course/archive/pagination/after' ); ?>
