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
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title><?php esc_html_e( 'Tutor Course Builder', 'tutor' ); ?></title>

	<?php wp_head(); ?>

	<style>
		#adminmenumain,
		#wpfooter {
			display: none !important;
		}
		#wpcontent {
			margin: 0 !important;
		}
		#wpbody-content {
			padding-bottom: 0px !important;
			float: none;
		}
		.notice {
			display: none;
		}
	</style>
</head>
<body <?php body_class(); ?>>
	<div id="tutor-course-builder"></div>
	<?php do_action( 'tutor_course_builder_footer' ); ?>
	<?php wp_footer(); ?>
</body>
</html>
