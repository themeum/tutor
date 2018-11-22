<?php
/**
 * @package @DOZENT
 * @since v.1.0.0
 */

if (isset($_GET['sub_page'])){
    $page = sanitize_text_field($_GET['sub_page']);
    include_once dozent()->path."views/pages/{$page}.php";
    return;
}

$teacherList = new \DOZENT\Quiz_Attempts_List();
$teacherList->prepare_items();
?>

<div class="wrap">
	<h2><?php _e('Quiz Attempts', 'dozent'); ?></h2>

	<form id="quiz_attempts-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$teacherList->search_box(__('Search', 'dozent'), 'quiz_attempts');
		$teacherList->display(); ?>
	</form>
</div>