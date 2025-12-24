<?php
/**
 * Tutor dashboard reviews.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

$reviews = array(
	array(
		'title'          => 'Course Title',
		'review_date'    => '2022-01-01',
		'rating'         => 5,
		'is_bundle'      => false,
		'review_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
	),
	array(
		'title'          => 'Course Title',
		'review_date'    => '2022-01-01',
		'rating'         => 5,
		'is_bundle'      => false,
		'review_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
	),
	array(
		'title'          => 'Bundle Title',
		'review_date'    => '2022-01-01',
		'rating'         => 5,
		'is_bundle'      => true,
		'review_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
	),
	array(
		'title'          => 'Course Title',
		'review_date'    => '2022-01-01',
		'rating'         => 5,
		'is_bundle'      => false,
		'review_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
	),
);

?>
<div class="tutor-user-reviews">
	<?php
	tutor_load_template(
		'demo-components.dashboard.components.profile-pages-header',
		array( 'page_title' => __( 'Reviews', 'tutor' ) )
	);
	?>
	<div class="tutor-profile-container">
		<div class="tutor-flex tutor-flex-column tutor-gap-5 tutor-mt-9">
			<?php foreach ( $reviews as $review ) : ?>
				<?php tutor_load_template( 'dashboard.reviews.review-card', array( 'review' => $review ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>
