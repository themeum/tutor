<?php
/*
@REST API for courses
@author : themeum
*/

namespace TUTOR;
use WP_REST_Request;
use WP_Query;

if( !defined ('ABSPATH'))
exit;

class REST_Course 
{
	use REST_Response;

	private $post_type = "courses";

	private $course_cat_tax = "course-category";

	private $course_tag_tax = "course-tag";

	public function __construct()
	{

	}


	/*
	*require rest request
	*return course info
	*pagination enable
	*category,tags terms included
	*/
	public function course(WP_REST_Request $request):object
	{
		$order = sanitize_text_field($request->get_param('order'));
		$orderby = sanitize_text_field($request->get_param('orderby'));
		$paged = sanitize_text_field($request->get_param('paged'));

		$args = array(
	        'post_type' => $this->post_type,
	        'post_status' => 'publish',
	        'posts_per_page' => 10, 
	        'paged' => $paged ? $paged : 1,
	        'order' => $order ? $order : 'ASC',
	        'orderby' => $orderby ? $orderby :'title'
		);

		$query = new WP_Query($args);
		$posts = $query->posts;//get posts
		
		//if post found
		if($posts)
		{
			$data = [
				'posts'=> [],
				'total_course' => $query->found_posts,
				'total_page' => $query->max_num_pages				
			];

			foreach($posts as $post)
			{
				$category = wp_get_post_terms($post->ID,$this->course_cat_tax);

				$tag = wp_get_post_terms($post->ID,$this->course_tag_tax);

				$post->course_category = $category;

				$post->course_tag = $tag;

				array_push($data['posts'], $post);

			}

			$response = array(
				'status_code'=> "success",
				"message"=> __('Course retrieved successfully','tutor'),
				'data'=> $data
			);

			return self::send($response);			
		}

		$response = array(
			'status_code'=> "not_found",
			"message"=> __('Course not found','tutor'),
			'data'=> []
		);

		return self::send($response);
		//return $query;
	}

	/*
	*require rest request
	*return post meta items
	*/
	function course_detail(WP_REST_Request $request):object
	{
		$post_id = $request->get_param('id');

		$detail = array(

			'course_settings' =>get_post_meta($post_id,'_tutor_course_settings',false),

			'course_price_type' =>get_post_meta($post_id,'_tutor_course_price_type',false),


			'course_duration' =>get_post_meta($post_id,'_course_duration',false),

			'course_level' =>get_post_meta($post_id,'_tutor_course_level',false),

			'course_benefits' =>get_post_meta($post_id,'_tutor_course_benefits',false),

			'course_requirements' =>get_post_meta($post_id,'_tutor_course_requirements',false),

			'course_target_audience' =>get_post_meta($post_id,'_tutor_course_target_audience',false),

			'course_material_includes' =>get_post_meta($post_id,'_tutor_course_material_includes',false),

			'video' =>get_post_meta($post_id,'_video',false),
			
			'disable_qa' =>get_post_meta($post_id,'_tutor_disable_qa','_video',false),
		);

		if($detail)
		{
			$response = array(
				'status_code'=> "course_detail",
				"message"=> __('Course detail retrieved successfully','tutor'),
				'data'=> $detail
			);

			return self::send($response);				
		}
		$response = array(
			'status_code'=> "course_detail",
			"message"=> __('Detail not found for given ID','tutor'),
			'data'=> []
		);		

		return self::send($response);

	}


}
?>