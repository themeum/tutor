<?php
/**
 * Addons class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.0.0
 */


namespace TUTOR;


if ( ! defined( 'ABSPATH' ) )
	exit;

class Addons {

	public function addons_page(){
		
		if ( false === ( $addons_themes_data = get_transient( 'tutor_addons_themes_data' ) ) ) {
			//Request New
			$api_endpoint = 'https://www.themeum.com/wp-json/addon-serve/v2/get-products';
			$response = wp_remote_post( $api_endpoint, array(
					'method' => 'POST',
					'timeout' => 45,
					'user-agent' => 'Tutor/'.TUTOR_VERSION.'; '.home_url( '/' ),
					'headers' => array(
						'wp_blog' => home_url( '/' )
					),
					'body' => array('plugin_slug' => 'tutor', 'wp_blog' => home_url( '/' )),
				)
			);

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				echo "Something went wrong: $error_message";
			} else {
				if (tutor_utils()->avalue_dot('body', $response) && tutor_utils()->avalue_dot('response.code', $response) == 200 ){
					$api_data = tutor_utils()->avalue_dot('body', $response);

					$addons_themes_data = array(
						'last_checked_time' => time(),
						'data' => $api_data,
					);
				}
			}

			//Save the Final api call result on the database
			set_transient( 'tutor_addons_themes_data', $addons_themes_data, 6 * HOUR_IN_SECONDS );
		}


		//Finally Show the View Page
		include tutor()->path.'views/pages/addons.php';
	}

}