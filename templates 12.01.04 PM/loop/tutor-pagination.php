<?php
/**
 * A single course loop pagination
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php do_action('tutor_course/archive/pagination/before');  ?>

<div class="tutor-pagination-wrap">
	<?php
	global $wp_query;
	$big = 999999999; // need an unlikely integer

	echo paginate_links( array(
		'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' => '?paged=%#%',
		'current' => max( 1, get_query_var('paged') ),
		'total' => $wp_query->max_num_pages
	) );
	?>
</div>

<?php do_action('tutor_course/archive/pagination/after');  ?>
