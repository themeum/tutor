<?php
/**
 * Manage Course API
 *
 * @package Tutor\RestAPI
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.7.1
 */

namespace TUTOR;

use WP_Query;
use WP_REST_Request;
use WP_REST_Response;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rest_Course class
 */
class REST_Course {

	use REST_Response;


	/**
	 * The post type associated with the course handler.
	 *
	 * @since 1.7.1
	 *
	 * @var string $post_type The post type for courses.
	 */
	private $post_type;

	/**
	 * The taxonomy for course categories.
	 *
	 * @since 1.7.1
	 *
	 * @var string $course_cat_tax The taxonomy for course categories.
	 */
	private $course_cat_tax = 'course-category';

	/**
	 * The taxonomy for course tags.
	 *
	 * @since 1.7.1
	 *
	 * @var string $course_tag_tax The taxonomy for course tags.
	 */
	private $course_tag_tax = 'course-tag';

	/**
	 * Constructor for the Tutor_Course_Handler class.
	 *
	 * Initializes the post type property.
	 *
	 * @since 1.7.1
	 */
	public function __construct() {
		$this->post_type = tutor()->course_post_type;
	}

	/**
	 * Course read API handler
	 *
	 * Get course list along with pagination, categories, tags
	 * author details, reviews
	 *
	 * @since 1.7.1
	 * @since 4.2.0 Whitelist order/orderby; isolate price sort path.
	 *
	 * @param WP_REST_Request $request request data.
	 *
	 * @return WP_REST_Response
	 */
	public function course( WP_REST_Request $request ) {
		$order      = self::sanitize_course_order( $request->get_param( 'order' ) );
		$orderby    = self::sanitize_course_orderby( $request->get_param( 'orderby' ) );
		$paged      = sanitize_text_field( $request->get_param( 'paged' ) );
		$categories = null;
		if ( isset( $request['categories'] ) ) {
			$categories = sanitize_term( explode( ',', $request['categories'] ), $this->course_cat_tax, $context = 'db' );
		}
		$tags = null;
		if ( isset( $request['tags'] ) ) {
			$tags = sanitize_term( explode( ',', $request['tags'] ), $this->course_tag_tax, $context = 'db' );
		}

		$post_per_page = tutor_utils()->get_option( 'pagination_per_page' );

		$args = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => $post_per_page,
			'paged'          => $paged ? $paged : 1,
			'order'          => $order,
			'orderby'        => $orderby,
		);

		if ( isset( $categories ) || isset( $tags ) ) {
			$args['tax_query'] = array(
				'relation' => 'OR',
				array(
					'taxonomy' => $this->course_cat_tax,
					'field'    => 'name',
					'terms'    => $categories,
				),
				array(
					'taxonomy' => $this->course_tag_tax,
					'field'    => 'name',
					'terms'    => $tags,

				),
			);
		}

		if ( 'price' === $orderby ) {
			$args = self::apply_price_order_args( $args );
		}

		$args = apply_filters( 'tutor_rest_course_query_args', $args );

		$query = new WP_Query( $args );

		// if post found.
		if ( count( $query->posts ) > 0 ) {
			$data = array(
				'posts'        => array(),
				'total_course' => $query->found_posts,
				'total_page'   => $query->max_num_pages,
			);

			foreach ( $query->posts as $post ) {
				if ( ! $post instanceof WP_Post ) {
					continue;
				}

				$is_guest = ! RestAuth::is_rest_authenticated();

				if ( $is_guest ) {
					$item = self::to_catalog_card_dto( $post );
				} else {
					$item = (object) $post->to_array();
					unset( $item->filter, $item->post_password );

					$category = wp_get_post_terms( $post->ID, $this->course_cat_tax );

					$tag = wp_get_post_terms( $post->ID, $this->course_tag_tax );

					$author = get_userdata( $post->post_author );

					if ( $author ) {
						$author_payload = (object) array(
							'ID'            => $author->ID,
							'display_name'  => $author->display_name,
							'user_nicename' => $author->user_nicename,
						);

						if ( RestAuth::can_view_user_private_fields( (int) $author->ID ) ) {
							$author_payload->user_login      = $author->user_login;
							$author_payload->user_email      = $author->user_email;
							$author_payload->user_registered = $author->user_registered;
						}

						$item->post_author = $author_payload;
					} else {
						$item->post_author = new \stdClass();
					}

					$thumbnail_size      = apply_filters( 'tutor_rest_course_thumbnail_size', 'post-thumbnail' );
					$item->thumbnail_url = get_the_post_thumbnail_url( $post->ID, $thumbnail_size );

					$item->additional_info = $this->course_additional_info( $post->ID );

					$item->ratings = tutor_utils()->get_course_rating( $post->ID );

					$item->course_category = $category;

					$item->course_tag = $tag;

					$item->price = get_post_meta( $post->ID, '_regular_price', true );
				}

				$item = apply_filters( 'tutor_rest_course_single_post', $item );

				array_push( $data['posts'], $item );
			}

			$response = array(
				'code'    => 'success',
				'message' => __( 'Course retrieved successfully', 'tutor' ),
				'data'    => $data,
			);

			return self::send( $response );
		}

		$response = array(
			'code'    => 'not_found',
			'message' => __( 'Course not found', 'tutor' ),
			'data'    => array(),
		);

		return self::send( $response );
	}

	/**
	 * Course Details API handler
	 *
	 * @since 1.7.1
	 *
	 * @param WP_REST_Request $request request params.
	 *
	 * @return WP_REST_Response
	 */
	public function course_detail( WP_REST_Request $request ) {
		$post_id = absint( $request->get_param( 'id' ) );

		$detail = $this->course_additional_info( $post_id, ! RestAuth::is_rest_authenticated() );
		if ( $detail ) {
			$response = array(
				'code'    => 'course_detail',
				'message' => __( 'Course detail retrieved successfully', 'tutor' ),
				'data'    => $detail,
			);
			return self::send( $response );
		}
		$response = array(
			'code'    => 'course_detail',
			'message' => __( 'Detail not found for given ID', 'tutor' ),
			'data'    => array(),
		);

		return self::send( $response );
	}

	/**
	 * Get course additional info
	 *
	 * @since 2.6.1
	 * @since 4.2.0 Guest responses omit internal course_settings.
	 *
	 * @param integer $post_id post id.
	 * @param bool    $for_guest Whether to redact instructor-only settings.
	 *
	 * @return array
	 */
	public function course_additional_info( int $post_id, $for_guest = false ) {
		$detail = array(
			'course_price_type'        => get_post_meta( $post_id, '_tutor_course_price_type', false ),
			'course_duration'          => get_post_meta( $post_id, '_course_duration', false ),
			'course_level'             => get_post_meta( $post_id, '_tutor_course_level', false ),
			'course_benefits'          => get_post_meta( $post_id, '_tutor_course_benefits', false ),
			'course_requirements'      => get_post_meta( $post_id, '_tutor_course_requirements', false ),
			'course_target_audience'   => get_post_meta( $post_id, '_tutor_course_target_audience', false ),
			'course_material_includes' => get_post_meta( $post_id, '_tutor_course_material_includes', false ),
			'video'                    => get_post_meta( $post_id, '_video', false ),
			'disable_qa'               => get_post_meta( $post_id, '_tutor_enable_qa', true ) != 'yes',
		);

		if ( ! $for_guest ) {
			$detail = array_merge(
				array(
					'course_settings' => get_post_meta( $post_id, '_tutor_course_settings', false ),
				),
				$detail
			);
		}

		return apply_filters( 'tutor_course_additional_info', $detail, $post_id, $for_guest );
	}

	/**
	 * Validate terms
	 *
	 * @since 1.7.1
	 *
	 * @param array $post post array.
	 *
	 * @return array validation errors.
	 */
	public function validate_terms( array $post ) {
		$categories = $post['categories'];
		$tags       = $post['tags'];

		$error = array();

		if ( ! is_array( $categories ) ) {
			array_push( $error, __( 'Categories field is not an array', 'tutor' ) );
		}

		if ( ! is_array( $tags ) ) {
			array_push( $error, __( 'Tags field is not an array', 'tutor' ) );
		}

		return $error;
	}



	/**
	 * Retrieve the course contents for a given course id
	 *
	 * @since 2.7.0
	 *
	 * @param WP_REST_Request $request request params.
	 *
	 * @return WP_REST_Response
	 */
	public function course_contents( WP_REST_Request $request ) {
		$course_id     = absint( $request->get_param( 'id' ) );
		$reveal_bodies = RestAuth::can_reveal_learning_payload( $course_id );
		$topics        = tutor_utils()->get_topics(
			$course_id,
			array(
				'post_status' => 'publish',
			)
		);

		if ( $topics->have_posts() ) {
			$data = array();
			foreach ( $topics->get_posts() as $topic ) {
				$current_topic = array(
					'id'       => $topic->ID,
					'title'    => $topic->post_title,
					'summary'  => $reveal_bodies ? $topic->post_content : '',
					'contents' => array(),
				);

				$topic_contents = tutor_utils()->get_course_contents_by_topic(
					$topic->ID,
					-1,
					array(
						'post_status' => 'publish',
					)
				);

				if ( $topic_contents->have_posts() ) {
					foreach ( $topic_contents->get_posts() as $content_post ) {
						array_push(
							$current_topic['contents'],
							$reveal_bodies
								? self::sanitize_course_content_item( $content_post )
								: self::sanitize_course_content_outline_item( $content_post )
						);
					}
				}

				array_push( $data, $current_topic );
			}

			$response = array(
				'code'    => 'success',
				'message' => __( 'Course contents retrieved successfully', 'tutor' ),
				'data'    => $data,
			);
			return self::send( $response );
		}

		$response = array(
			'code'    => 'not_found',
			'message' => __( 'Contents for this course with the given course id not found', 'tutor' ),
			'data'    => array(),
		);

		return self::send( $response );
	}

	/**
	 * Map a curriculum post to a student-safe DTO (no post_password/guid/etc).
	 *
	 * @since 4.2.0
	 *
	 * @param WP_Post $post Curriculum post.
	 *
	 * @return array
	 */
	private static function sanitize_course_content_item( WP_Post $post ) {
		return array(
			'ID'           => (int) $post->ID,
			'post_title'   => $post->post_title,
			'post_content' => $post->post_content,
			'post_name'    => $post->post_name,
			'post_type'    => $post->post_type,
			'menu_order'   => (int) $post->menu_order,
		);
	}

	/**
	 * Outline-only curriculum item (guest / locked parity with course-topics UI).
	 *
	 * @since 4.2.0
	 *
	 * @param WP_Post $post Curriculum post.
	 *
	 * @return array
	 */
	private static function sanitize_course_content_outline_item( WP_Post $post ) {
		$item = array(
			'ID'         => (int) $post->ID,
			'post_title' => $post->post_title,
			'post_name'  => $post->post_name,
			'post_type'  => $post->post_type,
			'menu_order' => (int) $post->menu_order,
		);

		$video_info = tutor_utils()->get_video_info( $post->ID );
		if ( $video_info && ! empty( $video_info->playtime ) ) {
			$item['duration'] = $video_info->playtime;
		}

		return $item;
	}

	/**
	 * Guest catalog card DTO (archive loop parity).
	 *
	 * @since 4.2.0
	 *
	 * @param WP_Post $post Course post.
	 *
	 * @return object
	 */
	private function to_catalog_card_dto( WP_Post $post ) {
		$author  = get_userdata( $post->post_author );
		$ratings = tutor_utils()->get_course_rating( $post->ID );

		$item = (object) array(
			'ID'               => (int) $post->ID,
			'post_title'       => $post->post_title,
			'post_name'        => $post->post_name,
			'thumbnail_url'    => get_the_post_thumbnail_url( $post->ID, apply_filters( 'tutor_rest_course_thumbnail_size', 'post-thumbnail' ) ),
			'price'            => get_post_meta( $post->ID, '_regular_price', true ),
			'post_author'      => $author
				? (object) array(
					'ID'           => (int) $author->ID,
					'display_name' => $author->display_name,
				)
				: new \stdClass(),
			'ratings'          => (object) array(
				'rating_count' => isset( $ratings->rating_count ) ? $ratings->rating_count : 0,
				'rating_avg'   => isset( $ratings->rating_avg ) ? $ratings->rating_avg : 0,
			),
			'course_category'  => self::terms_to_name_dto( wp_get_post_terms( $post->ID, $this->course_cat_tax ) ),
			'course_tag'       => self::terms_to_name_dto( wp_get_post_terms( $post->ID, $this->course_tag_tax ) ),
		);

		return $item;
	}

	/**
	 * Reduce WP_Term objects to id + name (card UI).
	 *
	 * @since 4.2.0
	 *
	 * @param array|\WP_Error $terms Terms.
	 *
	 * @return array
	 */
	private static function terms_to_name_dto( $terms ) {
		if ( ! is_array( $terms ) ) {
			return array();
		}

		$out = array();
		foreach ( $terms as $term ) {
			if ( ! is_object( $term ) ) {
				continue;
			}
			$out[] = (object) array(
				'term_id' => (int) $term->term_id,
				'name'    => $term->name,
				'slug'    => $term->slug,
			);
		}

		return $out;
	}

	/**
	 * Whitelist WP_Query order direction for the course list.
	 *
	 * @since 4.2.0
	 *
	 * @param mixed $order Raw request order value.
	 *
	 * @return string ASC or DESC.
	 */
	private static function sanitize_course_order( $order ) {
		$order   = strtoupper( sanitize_text_field( (string) $order ) );
		$allowed = array( 'ASC', 'DESC' );

		if ( in_array( $order, $allowed, true ) ) {
			return $order;
		}

		return 'ASC';
	}

	/**
	 * Whitelist WP_Query orderby for the course list.
	 *
	 * `price` is accepted here but applied only via apply_price_order_args().
	 *
	 * @since 4.2.0
	 *
	 * @param mixed $orderby Raw request orderby value.
	 *
	 * @return string Allowed orderby key.
	 */
	private static function sanitize_course_orderby( $orderby ) {
		$orderby = sanitize_text_field( (string) $orderby );
		$allowed = array(
			'title',
			'date',
			'modified',
			'ID',
			'name',
			'author',
			'menu_order',
			'price',
		);

		if ( in_array( $orderby, $allowed, true ) ) {
			return $orderby;
		}

		return 'title';
	}

	/**
	 * Apply the reviewed price-sort path (Tutor-linked WooCommerce products only).
	 *
	 * Client input never chooses post_type or meta_key; those are fixed here.
	 *
	 * @since 4.2.0
	 *
	 * @param array $args WP_Query arguments.
	 *
	 * @return array
	 */
	private static function apply_price_order_args( array $args ) {
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required to sort Tutor-linked WC products by numeric regular price.
		$args['post_type']  = 'product';
		$args['meta_key']   = '_regular_price';
		$args['orderby']    = 'meta_value_num';
		$args['meta_query'] = array(
			'relation' => 'AND',
			array(
				'key'   => '_tutor_product',
				'value' => 'yes',
			),
		);
		// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_query

		return $args;
	}
}
