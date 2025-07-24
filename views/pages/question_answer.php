<?php
/**
 * Question & Answer list page
 *
 * @package Tutor\Views
 * @subpackage Tutor\Q&A
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

use TUTOR\Input;
use Tutor\Models\CourseModel;
use TUTOR\Q_And_A;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$question_id = Input::get( 'question_id', 0, Input::TYPE_INT );
if ( $question_id > 0 ) {
	tutor_load_template_from_custom_path(
		tutor()->path . '/views/qna/qna-single.php',
		array(
			'question_id' => $question_id,
			'context'     => 'backend-dashboard-qna-single',
		)
	);
	return;
}

$qna_object     = tutor_lms()->q_and_a_list;
$qna            = $qna_object->get_items( $_GET );
$qna_list       = $qna['items'];
$qna_pagination = $qna['pagination'];

$filters = array(
	'bulk_action'  => true,
	'bulk_actions' => $qna_object->get_bulk_actions(),
	'ajax_action'  => 'tutor_qna_bulk_action',
	'filters'      => array(
		array(
			'label'      => __( 'Courses', 'tutor' ),
			'field_type' => 'select',
			'field_name' => 'course-id',
			'options'    => CourseModel::get_course_dropdown_options(),
			'searchable' => true,
			'value'      => Input::get( 'course-id', '' ),
		),
		array(
			'label'      => __( 'Status', 'tutor' ),
			'field_type' => 'select',
			'field_name' => 'data',
			'options'    => Q_And_A::tabs_key_value(),
			'value'      => Input::get( 'data', '' ),
		),
		array(
			'label'      => __( 'Date', 'tutor' ),
			'field_type' => 'date',
			'field_name' => 'date',
			'show_label' => true,
			'value'      => Input::get( 'date', '' ),
		),
	),
);

/**
 * Determine active tab
 */

$active_tab = Input::get( 'data', '' );

$navbar_data = array(
	'page_title' => __( 'Question & Answer', 'tutor' ),
);
?>

<div class="tutor-admin-wrap">
	<?php
		/**
		 * Load Templates with data.
		 */
		$navbar_template  = tutor()->path . 'views/elements/list-navbar.php';
		$filters_template = tutor()->path . 'views/elements/list-filters.php';
		tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
		tutor_load_template_from_custom_path( $filters_template, $filters );
	?>
	<div class="tutor-admin-container tutor-admin-container-lg">
		<div class="tutor-dashboard-list-table tutor-mt-16">
			<?php
				tutor_load_template_from_custom_path(
					tutor()->path . '/views/qna/qna-table.php',
					array(
						'qna_list'       => $qna_list,
						'context'        => 'backend-dashboard-qna-table',
						'qna_pagination' => $qna_pagination,
					)
				);
				?>
		</div>
	</div>
</div>
