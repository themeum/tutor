<?php
$studentList = new \LMS\Students_List();
$studentList->prepare_items();
?>


<div class="wrap">
	<h2><?php _e('Students', 'lms'); ?></h2>

	<form id="students-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$studentList->search_box(__('Search', 'lms'), 'students');
		$studentList->display(); ?>
	</form>
</div>