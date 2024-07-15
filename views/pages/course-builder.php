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
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'Tutor Course Builder', 'tutor' ); ?></title>
	<?php wp_print_styles(); ?>
</head>
<body data-tutor-context="<?php echo is_admin() ? 'backend' : 'frontend'; ?>">
	<div id="tutor-course-builder"></div>
	<?php
		do_action( 'tutor_course_builder_footer' );
		wp_print_footer_scripts();
	?>
</body>
</html>

