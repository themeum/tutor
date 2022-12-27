<?php
/**
 * Template for displaying price
 *
 * @package Tutor\Templates
 * @subpackage Single\Course
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$is_purchasable = tutor_utils()->is_course_purchasable();
$price          = apply_filters( 'get_tutor_course_price', null, get_the_ID() );

if ( $is_purchasable && $price ) {
	echo '<div class="price">' . $price . '</div>';//phpcs:ignore
} else {
	?>
	<div class="price">
		<?php esc_html_e( 'Free', 'tutor' ); ?>
	</div>
	<?php
}
?>
