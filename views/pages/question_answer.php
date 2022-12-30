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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( Input::has( 'question_id' ) ) {
	tutor_load_template_from_custom_path(
		tutor()->path . '/views/qna/qna-single.php',
		array(
			'question_id' => Input::get( 'question_id' ),
			'context'     => 'backend-dashboard-qna-single',
		)
	);
	return;
}

	$qna_object     = new \TUTOR\Question_Answers_List( false );
	$qna            = $qna_object->get_items( $_GET );
	$qna_list       = $qna['items'];
	$qna_pagination = $qna['pagination'];

	$filters = array(
		'bulk_action'   => true,
		'bulk_actions'  => $qna_object->get_bulk_actions(),
		'ajax_action'   => 'tutor_qna_bulk_action',
		'filters'       => true,
		'course_filter' => true,
	);

	/**
	 * Determine active tab
	 */

	$active_tab = Input::get( 'tab', 'all' );

	$navbar_data = array(
		'page_title' => __( 'Question & Answer', 'tutor' ),
		'tabs'       => \Tutor\Q_and_A::tabs_key_value(),
		'active'     => $active_tab,
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
		<div class="tutor-mt-24">
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
