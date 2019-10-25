<?php
/**
 * Created by PhpStorm.
 * User: mhshohel
 * Date: 22/10/19
 * Time: 12:57 PM
 */

namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;


class MigrationLD {

	public function __construct() {

		add_filter('tutor_tool_pages', array($this, 'tutor_tool_pages'));
	}

	public function tutor_tool_pages($pages){
		$isLearnDashData = get_option('learndash_data_settings');

		if ($isLearnDashData){
			$pages['migration_ld'] = __('LearnDash Migration', 'tutor');
		}

		return $pages;
	}

}