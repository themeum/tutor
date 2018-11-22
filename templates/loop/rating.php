<?php
/**
 * A single course loop rating
 *
 * @since v.1.0.0
 * @author themeum
 * @url https://themeum.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="dozent-loop-rating-wrap">
	<?php
	$course_rating = dozent_utils()->get_course_rating();
	dozent_utils()->star_rating_generator($course_rating->rating_avg);
	?>
</div>
