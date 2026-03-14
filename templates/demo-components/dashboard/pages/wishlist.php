<?php
/**
 * Tutor dashboard wishlist.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

use TUTOR\Icon;

$current_url = admin_url( 'admin.php?page=playground&subpage=dashboard' );

$page_nav_items = array(
	array(
		'type'    => 'dropdown',
		'icon'    => Icon::ENROLLED,
		'active'  => false,
		'options' => array(
			array(
				'label'  => __( 'Active', 'tutor' ),
				'icon'   => Icon::PLAY_LINE,
				'url'    => esc_url( add_query_arg( 'dashboard-page', 'courses', $current_url ) ),
				'active' => false,
			),
			array(
				'label'  => __( 'Enrolled', 'tutor' ),
				'icon'   => Icon::ENROLLED,
				'url'    => esc_url( add_query_arg( 'dashboard-page', 'courses', $current_url ) ),
				'active' => false,
			),
			array(
				'label'  => __( 'Complete', 'tutor' ),
				'icon'   => Icon::COMPLETED_CIRCLE,
				'url'    => esc_url( add_query_arg( 'dashboard-page', 'courses', $current_url ) ),
				'active' => false,
			),
		),
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Wishlist', 'tutor' ),
		'icon'   => Icon::WISHLIST,
		'url'    => esc_url( add_query_arg( 'dashboard-page', 'wishlist', $current_url ) ),
		'active' => true,
	),
	array(
		'type'   => 'link',
		'label'  => __( 'Quiz Attempts', 'tutor' ),
		'icon'   => Icon::QUIZ_2,
		'url'    => esc_url( add_query_arg( 'dashboard-page', 'quiz-attempts', $current_url ) ),
		'active' => false,
	),
);

// Sample wishlist data for demo purposes.
$wishlists = array(
	array(
		'image_url'       => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=300&fit=crop',
		'title'           => esc_html__( 'Advanced Concept Art for Science Fiction Productions', 'tutor' ),
		'rating_avg'      => 4.0,
		'rating_count'    => 605568,
		'learners'        => 1050,
		'instructor'      => 'Chris Dutto',
		'provider'        => 'Maven Analytics',
		'show_bestseller' => true,
		'price'           => '$49.00',
		'original_price'  => '$55.00',
	),
	array(
		'image_url'       => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=400&h=300&fit=crop',
		'title'           => esc_html__( 'Drawing for Beginners Level -2', 'tutor' ),
		'rating_avg'      => 4.5,
		'rating_count'    => 1234,
		'learners'        => 856,
		'instructor'      => 'Jane Smith',
		'provider'        => 'Creative Academy',
		'show_bestseller' => false,
		'price'           => '$39.00',
		'original_price'  => '',
	),
	array(
		'image_url'       => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=300&fit=crop',
		'title'           => esc_html__( 'Advanced Mathematics', 'tutor' ),
		'rating_avg'      => 4.8,
		'rating_count'    => 2345,
		'learners'        => 1923,
		'instructor'      => 'Dr. Michael Chen',
		'provider'        => 'Math Masters',
		'show_bestseller' => true,
		'price'           => '$59.00',
		'original_price'  => '$69.00',
	),
	array(
		'image_url'       => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400&h=300&fit=crop',
		'title'           => esc_html__( 'Web Development Masterclass', 'tutor' ),
		'rating_avg'      => 4.7,
		'rating_count'    => 3456,
		'learners'        => 2156,
		'instructor'      => 'Sarah Johnson',
		'provider'        => 'Tech Academy',
		'show_bestseller' => true,
		'price'           => '$79.00',
		'original_price'  => '$99.00',
	),
	array(
		'image_url'       => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=400&h=300&fit=crop',
		'title'           => esc_html__( 'Digital Marketing Fundamentals', 'tutor' ),
		'rating_avg'      => 4.3,
		'rating_count'    => 1890,
		'learners'        => 1456,
		'instructor'      => 'Mark Williams',
		'provider'        => 'Marketing Pro',
		'show_bestseller' => false,
		'price'           => '$45.00',
		'original_price'  => '',
	),
	array(
		'image_url'       => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=400&h=300&fit=crop',
		'title'           => esc_html__( 'Data Science and Analytics', 'tutor' ),
		'rating_avg'      => 4.9,
		'rating_count'    => 4567,
		'learners'        => 3245,
		'instructor'      => 'Dr. Emily Rodriguez',
		'provider'        => 'Data Insights',
		'show_bestseller' => true,
		'price'           => '$89.00',
		'original_price'  => '$109.00',
	),
	array(
		'image_url'       => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=400&h=300&fit=crop',
		'title'           => esc_html__( 'Photography Essentials', 'tutor' ),
		'rating_avg'      => 4.6,
		'rating_count'    => 2789,
		'learners'        => 1987,
		'instructor'      => 'Alex Thompson',
		'provider'        => 'Visual Arts',
		'show_bestseller' => false,
		'price'           => '$54.00',
		'original_price'  => '',
	),
	array(
		'image_url'       => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=400&h=300&fit=crop',
		'title'           => esc_html__( 'Business Strategy and Leadership', 'tutor' ),
		'rating_avg'      => 4.4,
		'rating_count'    => 1678,
		'learners'        => 1234,
		'instructor'      => 'Robert Martinez',
		'provider'        => 'Business School',
		'show_bestseller' => true,
		'price'           => '$69.00',
		'original_price'  => '$79.00',
	),
	array(
		'image_url'       => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=400&h=300&fit=crop',
		'title'           => esc_html__( 'Creative Writing Workshop', 'tutor' ),
		'rating_avg'      => 4.2,
		'rating_count'    => 1123,
		'learners'        => 987,
		'instructor'      => 'Lisa Anderson',
		'provider'        => 'Literary Arts',
		'show_bestseller' => false,
		'price'           => '$44.00',
		'original_price'  => '',
	),
);

?>
<div class="tutor-pt-7">
	<div class="tutor-dashboard-page-card">
		<div class="tutor-p-6 tutor-sm-p-2 tutor-border-b tutor-sm-border tutor-sm-rounded-2xl">
			<?php
			tutor_load_template(
				'core-components.nav',
				array(
					'items' => $page_nav_items,
					'size'  => 'lg',
				)
			);
			?>
		</div>
		<div class="tutor-dashboard-page-card-body tutor-dashboard-wishlist-wrapper">
			<?php if ( is_array( $wishlists ) && count( $wishlists ) ) : ?>
				<div class="tutor-wishlist-grid">
					<?php foreach ( $wishlists as $wishlist_item ) : ?>
						<div>
						<?php
						tutor_load_template(
							'demo-components.dashboard.components.course-card',
							array(
								'image_url'       => isset( $wishlist_item['image_url'] ) ? $wishlist_item['image_url'] : '',
								'title'           => isset( $wishlist_item['title'] ) ? $wishlist_item['title'] : '',
								'rating_avg'      => isset( $wishlist_item['rating_avg'] ) ? $wishlist_item['rating_avg'] : 0,
								'rating_count'    => isset( $wishlist_item['rating_count'] ) ? $wishlist_item['rating_count'] : 0,
								'learners'        => isset( $wishlist_item['learners'] ) ? $wishlist_item['learners'] : 0,
								'instructor'      => isset( $wishlist_item['instructor'] ) ? $wishlist_item['instructor'] : '',
								'instructor_url'  => '#',
								'provider'        => isset( $wishlist_item['provider'] ) ? $wishlist_item['provider'] : '',
								'show_bestseller' => isset( $wishlist_item['show_bestseller'] ) ? $wishlist_item['show_bestseller'] : false,
								'price'           => isset( $wishlist_item['price'] ) ? $wishlist_item['price'] : '',
								'original_price'  => isset( $wishlist_item['original_price'] ) ? $wishlist_item['original_price'] : '',
								'permalink'       => '#',
							)
						);
						?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="tutor-text-center tutor-py-16 tutor-text-muted">
					<?php esc_html_e( 'You have not added any courses to your wishlist yet.', 'tutor' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
