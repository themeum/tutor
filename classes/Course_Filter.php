<?php
/**
 * Manage Course Filter
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Course filter class
 *
 * @since 1.0.0
 */
class Course_Filter {
	/**
	 * Course Category
	 *
	 * @var string
	 */
	private $category = 'course-category';
	/**
	 * Course Tag
	 *
	 * @var string
	 */
	private $tag = 'course-tag';
	/**
	 * Course term id
	 *
	 * @var null|integer
	 */
	private $current_term_id = null;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $register_hook register hook or not.
	 * @return void|null
	 */
	public function __construct( $register_hook = true ) {
		if ( ! $register_hook ) {
			return;
		}
		add_action( 'wp_ajax_tutor_course_filter_ajax', array( $this, 'load_listing' ), 10, 0 );
		add_action( 'wp_ajax_nopriv_tutor_course_filter_ajax', array( $this, 'load_listing' ), 10, 0 );
		add_filter( 'term_link', __CLASS__ . '::filter_course_category_term_link', 10, 3 );
	}

	/**
	 * Load course listing
	 *
	 * @since 1.0.0
	 *
	 * @param mixed   $filters filters.
	 * @param boolean $return_filter return filterd data or not.
	 * @return mixed
	 */
	public function load_listing( $filters = null, $return_filter = false ) {
		! $return_filter ? tutils()->checking_nonce() : 0;

		$sanitized_post                                 = tutor_sanitize_data( null == $filters ? $_POST : $filters ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
		! is_array( $sanitized_post ) ? $sanitized_post = array() : 0;

		$default_per_page = tutils()->get_option( 'courses_per_page', 12 );
		$courses_per_page = (int) tutils()->array_get( 'course_per_page', $sanitized_post, $default_per_page );

		// Pagination arg.
		$current_page                   = (int) Input::sanitize_request_data( 'current_page', 1 );
		$paged                          = max( 1, $current_page );
		$sanitized_post['current_page'] = $paged;

		// Order arg.
		$order_by = 'post_date';
		$order    = 'DESC';

		if ( isset( $sanitized_post['course_order'] ) ) {
			switch ( $sanitized_post['course_order'] ) {
				case 'newest_first':
					$order_by = 'post_date';
					$order    = 'DESC';
					break;

				case 'oldest_first':
					$order_by = 'post_date';
					$order    = 'ASC';
					break;

				case 'course_title_az':
					$order_by = 'post_title';
					$order    = 'ASC';
					break;

				case 'course_title_za':
					$order_by = 'post_title';
					$order    = 'DESC';
					break;
			}
		}

		$args = array(
			'post_status'    => 'publish',
			'post_type'      => tutor()->course_post_type,
			'posts_per_page' => $courses_per_page,
			'paged'          => $paged,
			'orderby'        => $order_by,
			'order'          => $order,
			'tax_query'      => array(
				'relation' => 'OR',
			),
		);

		// Prepare taxonomy.
		foreach ( array( 'category', 'tag' ) as $taxonomy ) {

			$term_array                             = tutils()->array_get( 'tutor-course-filter-' . $taxonomy, $sanitized_post, array() );
			! is_array( $term_array ) ? $term_array = array( $term_array ) : 0;

			$term_array = array_filter(
				$term_array,
				function( $term_id ) {
					return is_numeric( $term_id );
				}
			);

			if ( count( $term_array ) > 0 ) {
				$tax_query = array(
					'taxonomy' => $this->$taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_array,
					'operator' => 'IN',
				);
				array_push( $args['tax_query'], $tax_query );
			}
		}

		// Prepare level and price type.
		$is_membership = get_tutor_option( 'monetize_by' ) == 'pmpro' && tutils()->has_pmpro();
		$level_price   = array();
		foreach ( array( 'level', 'price' ) as $type ) {

			if ( $is_membership && 'price' === $type ) {
				continue;
			}

			$type_array = tutils()->array_get( 'tutor-course-filter-' . $type, $sanitized_post, array() );
			$type_array = array_map( 'sanitize_text_field', ( is_array( $type_array ) ? $type_array : array( $type_array ) ) );

			if ( 'level' == $type && in_array( 'all_levels', $type_array ) ) {
				continue;
			}

			if ( count( $type_array ) > 0 ) {
				$level_price[] = array(
					'key'     => 'level' === $type ? '_tutor_course_level' : '_tutor_course_price_type',
					'value'   => $type_array,
					'compare' => 'IN',
				);
			}
		}
		count( $level_price ) ? $args['meta_query'] = $level_price : 0;

		$search_key              = sanitize_text_field( tutils()->array_get( 'keyword', $sanitized_post, null ) );
		$search_key ? $args['s'] = $search_key : 0;

		if ( isset( $sanitized_post['tutor_course_filter'] ) ) {
			switch ( $sanitized_post['tutor_course_filter'] ) {

				case 'newest_first':
					$args['orderby'] = 'post_date';
					$args['order']   = 'desc';
					break;

				case 'oldest_first':
					$args['orderby'] = 'post_date';
					$args['order']   = 'asc';
					break;

				case 'course_title_az':
					$args['orderby'] = 'post_title';
					$args['order']   = 'asc';
					break;

				case 'course_title_za':
					$args['orderby'] = 'post_title';
					$args['order']   = 'desc';
					break;
			}
		}

		// Return filters.
		$filters = apply_filters( 'tutor_course_filter_args', $args );
		if ( $return_filter ) {
			return $filters;
		}

		ob_start();

		query_posts( $filters );
		tutor_load_template( 'archive-course-init', array_merge( array( 'loop_content_only' => true ), $sanitized_post ) );

		wp_send_json_success( array( 'html' => ob_get_clean() ) );
		exit;
	}

	/**
	 * Get current term ID
	 *
	 * @since 1.0.0
	 * @return integer
	 */
	private function get_current_term_id() {

		if ( null === $this->current_term_id ) {
			$queried               = get_queried_object();
			$this->current_term_id = ( is_object( $queried ) && property_exists( $queried, 'term_id' ) ) ? $queried->term_id : false;
		}

		return $this->current_term_id;
	}

	/**
	 * Sort terms hierarchically
	 *
	 * @since 1.0.0
	 *
	 * @param array   $terms term list.
	 * @param integer $parent_id parent ID.
	 * @return array
	 */
	private function sort_terms_hierarchically( $terms, $parent_id = 0 ) {
		$term_array = array();

		foreach ( $terms as $term ) {
			if ( $term->parent == $parent_id ) {
				$term->children = $this->sort_terms_hierarchically( $terms, $term->term_id );
				$term_array[]   = $term;
			}
		}

		return $term_array;
	}

	/**
	 * Render terms hierarchically
	 *
	 * @since 1.0.0
	 *
	 * @param array  $terms term list.
	 * @param string $taxonomy taxonomy name.
	 * @return void
	 */
	private function render_terms_hierarchically( $terms, $taxonomy ) {
		$term_id = $this->get_current_term_id();

		foreach ( $terms as $term ) {
			?>
				<li class="tutor-list-item">
					<label>
						<input type="checkbox" class="tutor-form-check-input"  name="tutor-course-filter-<?php echo esc_attr( $taxonomy ); ?>" value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo esc_attr( $term->term_id == $term_id ? 'checked="checked"' : '' ); ?>/>
						<?php echo esc_html( $term->name ); ?>
					</label>
				</li>
				<?php isset( $term->children ) ? $this->render_terms_hierarchically( $term->children, $taxonomy ) : 0; ?>
			<?php
		}
	}

	/**
	 * Render terms
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy taxonomy name.
	 * @return void
	 */
	public function render_terms( $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $this->$taxonomy,
				'hide_empty' => true,
			)
		);
		$this->render_terms_hierarchically( $this->sort_terms_hierarchically( $terms ), $taxonomy );
	}

	/**
	 * Filter course-category term's permalink
	 *
	 * Add a query param so that course filter can work
	 *
	 * @param string   $termlink default term link.
	 * @param \WP_Term $term term obj.
	 * @param string   $taxonomy taxonomy.
	 *
	 * @return string customized term link
	 */
	public static function filter_course_category_term_link( string $termlink, \WP_Term $term, string $taxonomy ) {
		if ( 'course-category' === $taxonomy ) {
			$termlink = add_query_arg( 'tutor-course-filter-category', $term->term_id, $termlink );

		}
		return $termlink;
	}
}
