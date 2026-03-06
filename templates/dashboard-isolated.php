<?php
/**
 * Base template for isolated dashboard quiz attempt pages.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use TUTOR\Dashboard;

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php bloginfo( 'name' ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( '' ); ?>>
<?php
global $wp_query;

$dashboard_page    = tutor_utils()->array_get( 'tutor_dashboard_page', $wp_query->query_vars );
$dashboard_subpage = tutor_utils()->array_get( 'tutor_dashboard_sub_page', $wp_query->query_vars );
$page_template     = '';
$wrapper_class     = 'tutor-quiz-attempt-page-wrapper';
$back_url          = remove_query_arg( 'attempt_id' );
$close_url         = $back_url;

if ( Dashboard::QUIZ_ATTEMPTS_PAGE_SLUG === $dashboard_page ) {
	$page_template = tutor_get_template( 'dashboard.quiz-attempts.quiz-reviews' );
} elseif ( Dashboard::COURSES_PAGE_SLUG === $dashboard_page && Dashboard::MY_QUIZ_ATTEMPTS_SUBPAGE_SLUG === $dashboard_subpage ) {
	$page_template = tutor_get_template( 'dashboard.my-quiz-attempts.attempts-details' );
}
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>">
	<?php
	if ( $page_template && file_exists( $page_template ) ) {
		require_once $page_template;
	}
	?>
</div>
<?php wp_footer(); ?>
</body>
</html>
