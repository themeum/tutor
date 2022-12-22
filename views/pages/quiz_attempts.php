<?php
/**
 * Quiz Attempts List Template.
 *
 * @package Tutor\Views
 * @subpackage Tutor\QuizAttempts
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tutor\Cache\QuizAttempts;
use TUTOR\Input;
use Tutor\Models\QuizModel;

if ( is_numeric( Input::get( 'view_quiz_attempt_id' ) ) ) {
	include tutor()->path . 'views/pages/view_attempt.php';
	return;
}

$quiz_attempts = new TUTOR\Quiz_Attempts_List( false );

/**
 * Short able params
 */
$user_id   = Input::get( 'user_id', '' );
$course_id = Input::get( 'course-id', '' );
$order     = Input::get( 'order', 'DESC' );
$date      = Input::has( 'date' ) ? tutor_get_formated_date( 'Y-m-d', Input::get( 'date' ) ) : '';
$search    = Input::get( 'search', '' );

/**
 * Determine active tab
 */
$active_tab = Input::get( 'data', 'all' );

/**
 * Pagination data
 */
$paged    = Input::get( 'paged', 1, Input::TYPE_INT );
$per_page = tutor_utils()->get_option( 'pagination_per_page' );
$offset   = ( $per_page * $paged ) - $per_page;

$quiz_attempts_list = QuizModel::get_quiz_attempts( $offset, $per_page, $search, $course_id, $date, $order, $active_tab, false, true );
$total              = QuizModel::get_quiz_attempts( $offset, $per_page, $search, $course_id, $date, $order, $active_tab, true, true );

/**
 * Navbar data to make nav menu
 */
$navbar_data = array(
	'page_title' => $quiz_attempts->page_title,
	'tabs'       => $quiz_attempts->tabs_key_value( $user_id, $date, $search, $course_id ),
	'active'     => $active_tab,
);

$filters = array(
	'bulk_action'   => $quiz_attempts->bulk_action,
	'bulk_actions'  => $quiz_attempts->prpare_bulk_actions(),
	'ajax_action'   => 'tutor_quiz_attempts_bulk_action',
	'filters'       => true,
	'course_filter' => true,
);

?>

<div class="tutor-admin-wrap">
	<?php
		/**
		 * Load Templates with data.
		 */
		$navbar_template  = tutor()->path . 'views/elements/navbar.php';
		$filters_template = tutor()->path . 'views/elements/filters.php';
		tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
		tutor_load_template_from_custom_path( $filters_template, $filters );
	?>

	<div class="tutor-admin-body">
		<?php
			tutor_load_template_from_custom_path(
				tutor()->path . '/views/quiz/attempt-table.php',
				array(
					'attempt_list' => $quiz_attempts_list,
					'context'      => 'backend-dashboard-students-attempts',
				)
			);
			?>

		<div class="tutor-admin-page-pagination-wrapper tutor-mt-32">
			<?php
				/**
				 * Prepare pagination data & load template
				 */
			if ( $total > $per_page ) {
				$pagination_data     = array(
					'total_items' => $total,
					'per_page'    => $per_page,
					'paged'       => $paged,
				);
				$pagination_template = tutor()->path . 'views/elements/pagination.php';
				tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
			}
			?>
		</div>
	</div>
</div>
