<?php
/**
 * Created by PhpStorm.
 * User: mhshohel
 * Date: 17/9/18
 * Time: 1:33 PM
 */

namespace TUTOR;


class Tutor_Base {

	public $course_post_type;
	public $lesson_post_type;

	public function __construct() {

		$this->course_post_type = tutor()->course_post_type;
		$this->lesson_post_type = tutor()->lesson_post_type;

	}

}