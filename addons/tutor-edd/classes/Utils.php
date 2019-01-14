<?php
/**
 * Created by PhpStorm.
 * User: mhshohel
 * Date: 10/12/18
 * Time: 12:17 PM
 */

namespace TUTOR_EDD;


class Utils {

	public function get_edd_products(){
		global $wpdb;
		$query = $wpdb->get_results("SELECT ID, post_title from {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'download' ");

		return $query;
	}


}