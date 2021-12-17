<?php
/**
 * @package @TUTOR
 * @since v.1.0.0
 */

if (isset($_GET['question_id'])){
	tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-single.php', array(
		'question_id' => $_GET['question_id'],
		'context' => 'backend-dashboard-qna-single'
	));
    return;
}

$qna = (new \TUTOR\Question_Answers_List())->get_items();
$qna_list = $qna['items'];
$qna_pagination = $qna['pagination'];


/* $filters = array(
	'bulk_action'   => true,
	'bulk_actions'  => array('delete' => __('Delete', 'tutor')),
	'ajax_action'   => 'tutor_quiz_attempts_bulk_action',
	'filters'       => true,
	'course_filter' => true,
); */

?>

<?php
	/**
	 * Determine active tab
	 */
	$active_tab = isset( $_GET['data'] ) && $_GET['data'] !== '' ? esc_html__( $_GET['data'] ) : 'all';

	$navbar_data = array(
		'page_title' => __('Question & Answer', 'tutor'),
		'tabs'       => \Tutor\Q_and_A::tabs_key_value(),
		'active'     => $active_tab,
	);

	/**
	 * Load Templates with data.
	 */
	$navbar_template  = tutor()->path . 'views/elements/navbar.php';
	$filters_template = tutor()->path . 'views/elements/filters.php';
	tutor_load_template_from_custom_path( $navbar_template, $navbar_data );
	// tutor_load_template_from_custom_path( $filters_template, $filters );
?>


<div class="wrap">
	<?php 
		tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-table.php', array(
			'qna_list' => $qna_list,
			'context' => 'backend-dashboard-qna-table'
		));

		echo paginate_links( $qna_pagination );
	?>
	<div class="tutor-mt-30">
		<?php
			/**
			 * Prepare pagination data & load template
			 */
			$pagination_data = array(
				'total_items' => $qna_pagination['total_items'],
				'per_page'    => $qna_pagination['per_page'],
				'paged'       => $qna_pagination['paged'],
			);
			$pagination_template = tutor()->path . 'views/elements/pagination.php';
			tutor_load_template_from_custom_path( $pagination_template, $pagination_data );
		?>
	</div>
</div>