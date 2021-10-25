<?php
/**
 * Quiz Attempts List Template.
 *
 * @package Quiz Attempts List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TUTOR\Quiz_Attempts_List;
$quiz_attempts = new Quiz_Attempts_List();


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

$quiz_attempts_list = tutor_utils()->get_quiz_attempts($offset, $per_page, $search, $user_id, $date, $order, $course_id );
$total            = tutor_utils()->get_total_quiz_attempts($active_tab, $search, $user_id, $date, $course_id);

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
<div class="tutor-admin-page-wrapper">
	<?php
		/**
		 * Load Templates with data.
		 */
		$navbar_template  = tutor()->path . 'views/elements/navbar.php';
		$filters_template = tutor()->path . 'views/elements/filters.php';
		tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
		tutor_load_template_from_custom_path( $filters_template, $filters );
		
	?>
	<div class="tutor-ui-table-responsive tutor-mt-30 tutor-mr-20">
		<table class="tutor-ui-table my-quiz-attempts">
			<thead>
				<tr>
					<th>
						<div class="inline-flex-center color-text-subsued">
						<input id="tutor-bulk-checkbox-all" type="checkbox" class="tutor-form-check-input tutor-form-check-square" name="tutor-bulk-checkbox-all">
						</div>
					</th>
					<th>
						<div class="inline-flex-center color-text-subsued">
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
				<?php foreach ( $quiz_attempts_list as $list ) : ?>
				<tr>
					<td>
						<input id="tutor-admin-list-<?php esc_attr_e( $list->ID ); ?>" type="checkbox" class="tutor-form-check-input tutor-form-check-square tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php esc_attr_e( $list->ID ); ?>"/>
					</td>
					<td data-th="Quiz Info" class="column-fullwidth">
						<div class="td-statement-infor">
						<span class="text-regular-small color-text-primary">
						<?php echo $quiz_attempts->column_student( $list, 'attempt_ended_at' ); ?>
						</span>
						<p class="text-medium-body color-text-primary">
							<?php echo $quiz_attempts->column_quiz( $list, 'post_title' ); ?>
						</p>
						<span class="text-regular-small color-text-primary">
							Student: <?php echo $quiz_attempts->column_student_info( $list, 'display_name' ); ?>
						</span>
						</div>
					</td>
					<td data-th="Registration Date">
						<span class="color-text-primary text-regular-caption">
						<?php echo $quiz_attempts->column_course( $list, 'quiz' ); ?>
						</span>
					</td>
					<td data-th="Registration Date">
						<span class="color-text-primary text-regular-caption">
						<?php echo $quiz_attempts->column_total_questions( $list, 'total_questions' ); ?>
						</span>
					</td>
					<td data-th="Registration Date">
						<span class="color-text-primary text-regular-caption">
						<?php echo $quiz_attempts->column_earned_marks( $list, 'total_marks' ); ?>
						</span>
					</td>
					<td data-th="Registration Date">
						<span class="color-text-primary text-regular-caption">
						<?php echo $quiz_attempts->column_total_correct_answer( $list, 'total_correct_answer' ); ?>
						</span>
					</td>
					<td data-th="Registration Date">
						<span class="color-text-primary text-regular-caption">
						<?php echo esc_html( $list->user_email ); ?>
						</span>
					</td>
					</td>
					<td data-th="Registration Date">
						<span class="color-text-primary text-regular-caption">
						<?php echo $quiz_attempts->column_total_course( $list, 'total_course' ); ?>
						</span>
					</td>
					<td data-th="Course Taklen">
						<span class="color-text-primary text-medium-caption">
						<?php echo $quiz_attempts->column_status( $list, 'status' ); ?>
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
