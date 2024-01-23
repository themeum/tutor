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
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'Tutor Course Builder', 'tutor' ); ?></title>
</head>
<body>

	<div id="tutor-course-builder">React App Here</div>
	<!-- scripts -->
	<?php //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
	<script src="<?php echo esc_url( tutor()->url . 'assets/js/tutor-course-builder-v3.min.js?v=' . TUTOR_VERSION ); ?>"></script>
</body>
</html>

