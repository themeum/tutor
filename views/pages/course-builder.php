<?php
/**
 * View for course builder.
 *
 * @package Tutor\Views
 * @subpackage Pages
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_admin() ) {
	?>
	<style>
		#adminmenumain, #wpfooter, .notice, #tutor-page-wrap { display: none !important; }
		#wpcontent { margin: 0 !important; padding: 0 !important; }
		#wpbody-content { padding-bottom: 0px !important; float: none; }
	</style>
	<div id="tutor-course-builder"></div>
	<?php
	do_action( 'tutor_course_builder_footer' );
} else {
	?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title><?php esc_html_e( 'Tutor Course Builder', 'tutor' ); ?></title>
	<?php wp_head(); ?>
</head>
<body data-tutor-theme='light' <?php body_class(); ?>>
	<div id="tutor-course-builder"></div>
	<?php do_action( 'tutor_course_builder_footer' ); ?>
</body>
</html>

	<?php
	wp_footer();
	exit;
} ?>
