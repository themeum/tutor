<?php
/**
 * Integration class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Dozent
 * @since v.1.0.0
 */


namespace DOZENT;


class Theme_Compatibility {

	public function __construct() {
		$template = trailingslashit(get_template());
		$dozent_path = dozent()->path;

		$compatibility_theme_path = $dozent_path.'includes/theme-compatibility/'.$template.'functions.php';

		if (file_exists($compatibility_theme_path)){
			include $compatibility_theme_path;
		}

	}

}