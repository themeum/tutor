<?php
if ( ! defined( 'ABSPATH' ) )
	exit;


add_action('wp_enqueue_scripts', 'dozent_twentyfifteen_scripts');

if ( ! function_exists('dozent_twentyfifteen_scripts')){
	function dozent_twentyfifteen_scripts(){
		$dir_url = plugin_dir_url(__FILE__);
		wp_enqueue_style('dozent_twentyfifteen', $dir_url.'assets/css/style.css');
	}
}



