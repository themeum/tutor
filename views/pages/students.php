<?php
$studentList = new \TUTOR\Students_List();
$studentList->prepare_items();
?>


<div class="wrap">
	<h2><?php _e('Students', 'tutor'); ?></h2>

	<form id="students-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$studentList->search_box(__('Search', 'tutor'), 'students');
		$studentList->display(); ?>
	</form>
</div>