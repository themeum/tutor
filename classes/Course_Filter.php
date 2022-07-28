<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Course_Filter {
	private $category        = 'course-category';
	private $tag             = 'course-tag';
	private $current_term_id = null;

	function __construct($register_hook=true) {
		if(!$register_hook) {
			return;
		}
		add_action( 'wp_ajax_tutor_course_filter_ajax', array( $this, 'load_listing' ), 10, 0 );
		add_action( 'wp_ajax_nopriv_tutor_course_filter_ajax', array( $this, 'load_listing' ), 10, 0 );
	}

	public function load_listing($filters=null, $return_filter=false) {
		!$return_filter ? tutils()->checking_nonce() : 0;
		$_post = tutor_sanitize_data($filters==null ? $_POST : $filters);
		!is_array( $_post ) ? $_post=array() : 0;

		$default_per_page = tutils()->get_option( 'courses_per_page', 12 );
		$courses_per_page = (int) tutils()->array_get( 'course_per_page', $_post, $default_per_page );

		// Pagination arg
		$page = ( isset( $_post['current_page'] ) && is_numeric( $_post['current_page'] ) && $_post['current_page'] > 0 ) ? $_post['current_page'] : 1;
		$_post['current_page'] = $page;

		// Order arg
		$order_by = 'ID';
		$order = 'DESC';

		if(isset($_post['course_order'])) {
			switch($_post['course_order']) {
				case 'newest_first' : 
					$order_by = 'ID';
					$order = 'DESC';
					break;

				case 'oldest_first' : 
					$order_by = 'ID';
					$order = 'ASC';
					break;
				
				case 'course_title_az' : 
					$order_by = 'post_title';
					$order = 'ASC';
					break;
				
				case 'course_title_za' : 
					$order_by = 'post_title';
					$order = 'DESC';
					break;
			}
		}
		
		$args = array(
			'post_status'    => 'publish',
			'post_type'      => tutor()->course_post_type,
			'posts_per_page' => $courses_per_page,
			'paged'          => (int)$page,
			'orderby'		 => $order_by,
			'order'			 => $order,
			'tax_query'      => array(
				'relation' => 'OR',
			),
		);

		// Prepare taxonomy
		foreach ( array( 'category', 'tag' ) as $taxonomy ) {

			$term_array = tutils()->array_get( 'tutor-course-filter-' . $taxonomy, $_post, array() );
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

		// Prepare level and price type
		$is_membership = get_tutor_option( 'monetize_by' ) == 'pmpro' && tutils()->has_pmpro();
		$level_price   = array();
		foreach ( array( 'level', 'price' ) as $type ) {

			if ( $is_membership && 'price' === $type ) {
				continue;
			}

			$type_array = tutils()->array_get( 'tutor-course-filter-' . $type, $_post, array() );
			$type_array = array_map( 'sanitize_text_field', ( is_array( $type_array ) ? $type_array : array( $type_array ) ) );

			if($type=='level' && in_array('all_levels', $type_array)) {
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

		$search_key = sanitize_text_field( tutils()->array_get( 'keyword', $_post, null ) );
		$search_key ? $args['s'] = $search_key : 0;

		if ( isset( $_post['tutor_course_filter'] ) ) {
			switch ( $_post['tutor_course_filter'] ) {

				case 'newest_first':
					$args['orderby'] = 'ID';
					$args['order']   = 'desc';
					break;

				case 'oldest_first':
					$args['orderby'] = 'ID';
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

		// Return filters
		$filters = apply_filters( 'tutor_course_filter_args', $args );
		if($return_filter){
			return $filters;
		}

		ob_start();

		query_posts( $filters );
		tutor_load_template( 'archive-course-init', array_merge( array('loop_content_only' => true), $_post ));

		wp_send_json_success( array('html' => ob_get_clean()) );
		exit;
	}

	private function get_current_term_id() {

		if ( $this->current_term_id === null ) {
			$queried               = get_queried_object();
			$this->current_term_id = ( is_object( $queried ) && property_exists( $queried, 'term_id' ) ) ? $queried->term_id : false;
		}

		return $this->current_term_id;
	}

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

	private function render_terms_hierarchically( $terms, $taxonomy ) {

		$term_id = $this->get_current_term_id();

		foreach($terms as $term){
            ?>
                <li class="tutor-list-item">
                    <label>
						<input type="checkbox" class="tutor-form-check-input"  name="tutor-course-filter-<?php echo $taxonomy; ?>" value="<?php echo $term->term_id; ?>" <?php echo $term->term_id==$term_id ? 'checked="checked"' : ''; ?>/>
						<?php echo $term->name; ?>
                    </label>
				</li>
                <?php isset($term->children) ? $this->render_terms_hierarchically($term->children, $taxonomy) : 0; ?>
            <?php
        }
	}

	public function render_terms( $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $this->$taxonomy,
				'hide_empty' => true,
			)
		);
		$this->render_terms_hierarchically( $this->sort_terms_hierarchically( $terms ), $taxonomy );
	}
}
