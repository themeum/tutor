<?php
/**
 * Tutor dashboard reviews.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Input;

// Pagination Variable.
$pagination_per_page = tutor_utils()->get_option( 'pagination_per_page', 20 );
$current_page        = max( 1, Input::get( 'current_page', 0, Input::TYPE_INT ) );
$offset              = ( $current_page - 1 ) * $pagination_per_page;


$all_reviews    = tutor_utils()->get_reviews_by_user( 0, $offset, $pagination_per_page, true );
$review_count   = $all_reviews->count;
$reviews        = $all_reviews->results;
$received_count = tutor_utils()->get_reviews_by_instructor( 0, 0, 0 )->count;

echo '<pre>';
print_r( $reviews );
echo '</pre>';
?>
<div class="tutor-user-reviews">
	<div class="tutor-profile-container">
		<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-mt-9">
			<?php tutor_load_template( 'demo-components.dashboard.components.review-card' ); ?>
			<?php tutor_load_template( 'demo-components.dashboard.components.review-card' ); ?>
			<?php tutor_load_template( 'demo-components.dashboard.components.review-card' ); ?>
			<?php tutor_load_template( 'demo-components.dashboard.components.review-card' ); ?>
		</div>
	</div>
</div>
