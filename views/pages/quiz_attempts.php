<?php
/**
 * Quiz Attempts List Template.
 *
 * @package Quiz Attempts List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (isset($_GET['view_quiz_attempt_id']) && is_numeric($_GET['view_quiz_attempt_id'])){
    include tutor()->path."views/pages/view_attempt.php";
    return;
}

$quiz_attempts = new TUTOR\Quiz_Attempts_List(false);

/**
 * Short able params
 */
$user_id   = isset( $_GET['user_id'] ) ? $_GET['user_id'] : '';
$course_id = isset( $_GET['course-id'] ) ? $_GET['course-id'] : '';
$order     = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
$date      = isset( $_GET['date'] ) ? tutor_get_formated_date( 'Y-m-d', $_GET['date'] ) : '';
$search    = isset( $_GET['search'] ) ? $_GET['search'] : '';

/**
 * Determine active tab
 */
$active_tab = isset( $_GET['data'] ) && $_GET['data'] !== '' ? esc_html__( $_GET['data'] ) : 'all';

/**
 * Pagination data
 */
$paged    = ( isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) && $_GET['paged'] >= 1 ) ? $_GET['paged'] : 1;
$per_page = tutor_utils()->get_option( 'pagination_per_page' );
$offset   = ( $per_page * $paged ) - $per_page;

$quiz_attempts_list = tutor_utils()->get_quiz_attempts($offset, $per_page, $search, $course_id, $date, $order, $active_tab );
$total = tutor_utils()->get_quiz_attempts($offset, $per_page, $search, $course_id, $date, $order, $active_tab, true );

/**
 * Navbar data to make nav menu
 */
$navbar_data = array(
	'page_title' => $quiz_attempts->page_title,
	'tabs'       => $quiz_attempts->tabs_key_value(  $user_id, $date, $search, $course_id ),
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

<?php
	/**
	 * Load Templates with data.
	 */
	$navbar_template  = tutor()->path . 'views/elements/navbar.php';
	$filters_template = tutor()->path . 'views/elements/filters.php';
	tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
	tutor_load_template_from_custom_path( $filters_template, $filters );
?>

<div class="wrap">
	<?php
		tutor_load_template_from_custom_path(tutor()->path . '/views/quiz/attempt-table.php', array(
			'attempt_list' => $quiz_attempts_list,
			'context' => 'backend-dashboard-students-attempts'
		));
	?>
	<div class="tutor-admin-page-pagination-wrapper tutor-mt-32">
		<?php
			/**
			 * Prepare pagination data & load template
			 */
			if($total > $per_page) {
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
