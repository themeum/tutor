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
		if (tutor_utils()->count($qna_list)) {
			tutor_load_template_from_custom_path(tutor()->path . '/views/qna/qna-table.php', array(
				'qna_list' => $qna_list,
				'context' => 'backend-dashboard-qna-table'
			));

			echo paginate_links( $qna_pagination );
		}
	?>
</div>