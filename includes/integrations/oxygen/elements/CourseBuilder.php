<?php
namespace Oxygen\TutorElements;

class CourseBuilder extends \OxygenTutorElements{

	function name() {
		return 'Course Builder';
	}


	function render($options, $defaults, $content) {
		global $post;

		$course_post_type = tutor()->course_post_type;


		// add body class
		add_filter('body_class', array($this, "tutor_body_class"));
		/*
				if (isset($options['product_id']) && $options['product_id']) {

					$override_product = wc_get_product($options['product_id']);

					if ($override_product) {
						$product = $override_product;

						// update global post
						$post = get_post($options['product_id']);
						setup_postdata( $post );

						// enqueue woo gallery scripts
						// taken from WC_Frontend_Scripts::load_scripts
						if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
							wp_enqueue_script( 'zoom' );
						}
						if ( current_theme_supports( 'wc-product-gallery-slider' ) ) {
							wp_enqueue_script( 'flexslider' );
						}
						if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
							wp_enqueue_script( 'photoswipe-ui-default' );
							wp_enqueue_style( 'photoswipe-default-skin' );
							add_action( 'wp_footer', 'woocommerce_photoswipe' );
						}
						wp_enqueue_script( 'wc-single-product' );
					}

				}*/


		if ($content) {

			?>

			<div id="tutor-course-<?php the_ID(); ?>" <?php tutor_container_classes(); ?>>

				<?php do_action('tutor_course/single/before/wrap'); ?>

				<div class='oxy-course-wrapper-inner oxy-inner-content'>
					<?php echo do_shortcode($content); ?>
				</div>

				<?php do_action('tutor_course/single/after/wrap'); ?>

			</div>

			<?php

			// what about handling html structured data, i.e. WC_Structured_Data::generate_product_data?

		} else {


			global $post;
			setup_postdata($post);


			global $wp_query;

			if ($wp_query->is_single && ! empty($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === $course_post_type){
				$student_must_login_to_view_course = tutor_utils()->get_option('student_must_login_to_view_course');
				if ($student_must_login_to_view_course){
					if ( ! is_user_logged_in() ) {
						return tutor_get_template( 'login' );
					}
				}

				wp_reset_query();


				?>



				<?php do_action('tutor_course/single/before/wrap'); ?>

				<div <?php tutor_post_class('tutor-full-width-course-top tutor-course-top-info tutor-page-wrap'); ?>>
					<div class="tutor-container">
						<div class="tutor-row">
							<div class="tutor-col-8 tutor-col-md-100">
								<?php do_action('tutor_course/single/before/inner-wrap'); ?>
								<?php tutor_course_lead_info(); ?>
								<?php tutor_course_content(); ?>
								<?php tutor_course_benefits_html(); ?>
								<?php tutor_course_topics(); ?>
								<?php tutor_course_instructors_html(); ?>
								<?php tutor_course_target_reviews_html(); ?>
								<?php do_action('tutor_course/single/after/inner-wrap'); ?>
							</div> <!-- .tutor-col-8 -->

							<div class="tutor-col-4">
								<div class="tutor-single-course-sidebar">
									<?php do_action('tutor_course/single/before/sidebar'); ?>
									<?php tutor_course_enroll_box(); ?>
									<?php tutor_course_requirements_html(); ?>
									<?php tutor_course_tags_html(); ?>
									<?php tutor_course_target_audience_html(); ?>
									<?php do_action('tutor_course/single/after/sidebar'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>

				<?php do_action('tutor_course/single/after/wrap'); ?>
				<?php




			}




		}


		wp_reset_query();

		global $oxy_vsb_use_query;

		if($oxy_vsb_use_query) {
			$oxy_vsb_use_query->reset_postdata();
		}
	}


	public function tutor_body_class($classes) {

		$classes[] = 'tutor';
		return $classes;
	}



	function button_place() {
		return "tutor::single";
	}

}


new CourseBuilder();