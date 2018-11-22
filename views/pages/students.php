<?php
$studentList = new \DOZENT\Students_List();
$studentList->prepare_items();
?>


<div class="wrap">
	<h2><?php _e('Students', 'dozent'); ?></h2>

	<form id="students-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$studentList->search_box(__('Search', 'dozent'), 'students');
		$studentList->display(); ?>
	</form>
</div>