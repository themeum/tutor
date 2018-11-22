<?php
/**
 * Created by PhpStorm.
 * User: mhshohel
 * Date: 17/9/18
 * Time: 1:33 PM
 */

namespace DOZENT;


class Dozent_Base {

	public $course_post_type;
	public $lesson_post_type;

	public $lesson_base_permalink;

	public function __construct() {

		$this->course_post_type = dozent()->course_post_type;
		$this->lesson_post_type = dozent()->lesson_post_type;

		//Lesson Permalink
		$this->lesson_base_permalink = dozent_utils()->get_option('lesson_permalink_base');
		if ( ! $this->lesson_base_permalink){
			$this->lesson_base_permalink = $this->lesson_post_type;
		}

	}

}