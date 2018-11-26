<?php
/**
 * @package @TUTOR
 * @since v.1.0.0
 */

if (isset($_GET['sub_page'])){
    $page = sanitize_text_field($_GET['sub_page']);
    include_once tutor()->path."views/pages/{$page}.php";
    return;
}

$instructorList = new \TUTOR\Quiz_Attempts_List();
$instructorList->prepare_items();
?>

<div class="wrap">
	<h2><?php _e('Quiz Attempts', 'tutor'); ?></h2>

	<form id="quiz_attempts-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$instructorList->search_box(__('Search', 'tutor'), 'quiz_attempts');
		$instructorList->display(); ?>
	</form>
</div>