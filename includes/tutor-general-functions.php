<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Tutor general Functions
 */


if ( ! function_exists('tutor_withdrawal_methods')){
	function tutor_withdrawal_methods(){
		$withdraw = new \TUTOR\Withdraw();

		return $withdraw->available_withdraw_methods;
	}
}


if ( ! function_exists('tutor_placeholder_img_src')) {
	function tutor_placeholder_img_src() {
		$src = tutor()->url . 'assets/images/placeholder.jpg';
		return apply_filters( 'tutor_placeholder_img_src', $src );
	}
}

/**
 * @return string
 *
 * Get course categories selecting UI
 *
 * @since v.1.3.4
 */

if ( ! function_exists('tutor_course_categories_dropdown')){
	function tutor_course_categories_dropdown($args = array()){

		$default = array(
			'name'  => 'tutor_course_category',
			'multiple' => true,
		);

		$args = apply_filters('tutor_course_categories_dropdown_args', array_merge($default, $args));

		$multiple_select = '';

		if (tutor_utils()->array_get('multiple', $args)){
			if (isset($args['name'])){
				$args['name'] = $args['name'].'[]';
			}
			$multiple_select = "multiple='multiple'";
		}

		extract($args);

		$categories = tutor_utils()->get_course_categories();

		$output = '';
		$output .= "<select name='{$name}' {$multiple_select}>";
		$output .= "<option value=''>Select a category</option>";
		$output .= _generate_categories_dropdown_option($categories);
		$output .= "</select>";

		return $output;
	}
}

/**
 * @param $categories
 * @param string $parent_name
 *
 * @return string
 *
 * Get selecting options, recursive supports
 *
 * @since v.1.3.4
 */

if ( ! function_exists('_generate_categories_dropdown_option')){
	function _generate_categories_dropdown_option($categories, $parent_name = ''){
		$output = '';

		if (tutor_utils()->count($categories)) {
			foreach ( $categories as $category_id => $category ) {
				$childrens = tutor_utils()->array_get( 'children', $category );
				$output .= "<option value='{$category->term_id}'>{$parent_name}{$category->name} </option>";

				if ( tutor_utils()->count( $childrens ) ) {
					$parent_name.= "&nbsp;&nbsp;&nbsp;&nbsp;";
					$output .= _generate_categories_dropdown_option( $childrens, $parent_name );
				}
			}
		}
		return $output;
	}
}

/**
 * @param array $args
 *
 * @return string
 *
 * Generate course categories checkbox
 * @since v.1.3.4
 */

if ( ! function_exists('tutor_course_categories_checkbox')){
	function tutor_course_categories_checkbox($args = array()){
		$default = array(
			'name'  => 'tutor_course_category',
		);

		$args = apply_filters('tutor_course_categories_checkbox_args', array_merge($default, $args));

		if (isset($args['name'])){
			$args['name'] = $args['name'].'[]';
		}

		extract($args);

		$categories = tutor_utils()->get_course_categories();

		$output = '';
		$output .= __tutor_generate_categories_checkbox($categories, '', $args);

		return $output;
	}
}

/**
 * @param $categories
 * @param string $parent_name
 * @param array $args
 *
 * @return string
 *
 * Internal function to generate course categories checkbox
 *
 * @since v.1.3.4
 */
if ( ! function_exists('__tutor_generate_categories_checkbox')){
	function __tutor_generate_categories_checkbox($categories, $parent_name = '', $args = array()){
		$output = '';
		$input_name = tutor_utils()->array_get('name', $args);

		if (tutor_utils()->count($categories)) {
			foreach ( $categories as $category_id => $category ) {
				$childrens = tutor_utils()->array_get( 'children', $category );
				$output .= "<p class='course-category-checkbox'><label> {$parent_name} <input type='checkbox' name='{$input_name}' value='{$category->term_id}' /> {$category->name} </label> </p>";

				if ( tutor_utils()->count( $childrens ) ) {
					$parent_name.= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					$output .= __tutor_generate_categories_checkbox( $childrens, $parent_name, $args );
				}
			}
		}
		return $output;
	}
}