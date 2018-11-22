<?php
if ( ! defined( 'ABSPATH' ) )
	exit;


add_action('wp_enqueue_scripts', 'dozent_twentyseventeen_scripts');

if ( ! function_exists('dozent_twentyseventeen_scripts')){
	function dozent_twentyseventeen_scripts(){
		$dir_url = plugin_dir_url(__FILE__);
		wp_enqueue_style('dozent_twentyseventeen', $dir_url.'assets/css/style.css');
	}
}



