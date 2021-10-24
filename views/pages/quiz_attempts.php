<?php
/**
 * Quiz List Template.
 *
 * @package Quiz List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Quiz_Attempts_List;
$quizs = new Quiz_Attempts_List();


/**
 * Short able params
 */
$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : '';
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

$quiz_list = tutor_utils()->get_quiz_attempts_by_course_ids( $current_page * $per_page, $per_page, $get_course_ids, $search_filter, $course_filter, $date_filter, $order_filter );
$total            = tutor_utils()->get_total_quiz_attempts_by_course_ids($get_course_ids);

/**
 * Navbar data to make nav menu
 */
$navbar_data = array(
	'page_title' => $quizs->page_title,
	'tabs'       => $quizs->tabs_key_value(  $user_id, $date, $search, $course_id ),
	'active'     => $active_tab,
);

$filters = array(
	'bulk_action'   => $quizs->bulk_action,
	'bulk_actions'  => $quizs->prpare_bulk_actions(),
	'ajax_action'   => 'tutor_quizs_bulk_action',
	'filters'       => true,
	'course_filter' => true,
);

?>
<div class="tutor-admin-page-wrapper">
	<?php
		/**
		 * Load Templates with data.
		 */
		//$navbar_template  = tutor()->path . 'views/elements/navbar.php';
		$filters_template = tutor()->path . 'views/elements/filters.php';
		//tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
		tutor_load_template_from_custom_path( $filters_template, $filters );
		
	?>

	

	<div class="tutor-ui-table-responsive tutor-mt-30 tutor-mr-20">
		<table class="tutor-ui-table table-students">
			<thead>
			<tr>
				<th>
				<div class="inline-flex-center color-text-subsued">
				<input id="tutor-bulk-checkbox-all" type="checkbox" class="tutor-form-check-input tutor-form-check-square" name="tutor-bulk-checkbox-all">
					<span class="text-regular-small tutor-ml-5"> <?php esc_html_e( 'Quiz Info', 'tutor-pro' ); ?></span>
					<span class="tutor-v2-icon-test icon-ordering-a-to-z-filled"></span>
				</div>
				</th>
				<th>
				<div class="inline-flex-center color-text-subsued">
					<span class="text-regular-small"><?php esc_html_e( 'Course', 'tutor-pro' ); ?></span>
					<span class="tutor-v2-icon-test icon-order-down-filled"></span>
				</div>
				</th>
				<th>
				<div class="inline-flex-center color-text-subsued">
					<span class="text-regular-small"><?php esc_html_e( 'Question', 'tutor-pro' ); ?></span>
					<span class="tutor-v2-icon-test icon-order-down-filled"></span>
				</div>
				</th>
				<th>
				<div class="inline-flex-center color-text-subsued">
					<span class="text-regular-small"><?php esc_html_e( 'Total Marks', 'tutor-pro' ); ?></span>
					<span class="tutor-v2-icon-test icon-order-down-filled"></span>
				</div>
				</th>
				<th>
				<div class="inline-flex-center color-text-subsued">
					<span class="text-regular-small"><?php esc_html_e( 'Correct Answer', 'tutor-pro' ); ?></span>
					<span class="tutor-v2-icon-test icon-order-down-filled"></span>
				</div>
				</th>
				<th>
				<div class="inline-flex-center color-text-subsued">
					<span class="text-regular-small"><?php esc_html_e( 'Incorrect Answer', 'tutor-pro' ); ?></span>
					<span class="tutor-v2-icon-test icon-order-down-filled"></span>
				</div>
				</th>
				<th>
				<div class="inline-flex-center color-text-subsued">
					<span class="text-regular-small"><?php esc_html_e( 'Earned Marks', 'tutor-pro' ); ?></span>
					<span class="tutor-v2-icon-test icon-order-down-filled"></span>
				</div>
				</th>
				<th>
				<div class="inline-flex-center color-text-subsued">
					<span class="text-regular-small"><?php esc_html_e( 'Result', 'tutor-pro' ); ?></span>
					<span class="tutor-v2-icon-test icon-order-down-filled"></span>
				</div>
				</th>
				<th class="tutor-shrink"></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $quiz_list as $list ) : ?>
			<tr>
				<td data-th="Student">
				<div class="td-avatar">
				<input id="tutor-admin-list-<?php esc_attr_e( $list->ID ); ?>" type="checkbox" class="tutor-form-check-input tutor-form-check-square tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php esc_attr_e( $list->ID ); ?>"/>
				<span class="color-text-primary text-regular-caption">
				<?php echo $quizs->column_student( $list, 'student' ); ?>
				</span>
				</div>
				</td>
				<td data-th="Registration Date">
				<span class="color-text-primary text-regular-caption">
				<?php echo esc_html( $list->quiz ); ?>
				</span>
				</td>
				</td>
				<td data-th="Registration Date">
				<span class="color-text-primary text-regular-caption">
				<?php echo esc_html( $list->quiz ); ?>
				</span>
				</td>
				<td data-th="Registration Date">
				<span class="color-text-primary text-regular-caption">
				<?php echo esc_html( $list->quiz ); ?>
				</span>
				</td>
				<td data-th="Registration Date">
				<span class="color-text-primary text-regular-caption">
				<?php echo esc_html( $list->quiz ); ?>
				</span>
				</td>
				<td data-th="Registration Date">
				<span class="color-text-primary text-regular-caption">
				<?php echo esc_html( $list->quiz ); ?>
				</span>
				</td>
				<td data-th="Registration Date">
				<span class="color-text-primary text-regular-caption">
				<?php echo esc_html( $list->quiz ); ?>
				</span>
				</td>
				<td data-th="Course Taklen">
				<span class="color-text-primary text-medium-caption">
				<?php echo esc_html( $list->quiz ); ?>
				</span>
				</td>
				<td data-th="URL">
				<div class="inline-flex-center td-action-btns">
					<?php $edit_link = add_query_arg( 'user_id', $list->ID, self_admin_url( 'user-edit.php'));
						
					?>
					<a href="<?php echo $edit_link; ?>" 
					class="btn-outline tutor-btn">
					<?php esc_html_e( 'Details', 'tutor-pro' ); ?>
					</a>
				</div>
				</td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	<div class="tutor-admin-page-pagination-wrapper">
		<?php
			/**
			 * Prepare pagination data & load template
			 */
			$pagination_data     = array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'paged'       => $paged,
			);
			$pagination_template = tutor()->path . 'views/elements/pagination.php';
			tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
			?>
	</div>
</div>
