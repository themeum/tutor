<?php
$teacherList = new \TUTOR\Teachers_List();
$teacherList->prepare_items();
?>


<div class="wrap">
	<h2><?php _e('Teachers', 'tutor'); ?></h2>

	<form id="students-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$teacherList->search_box(__('Search', 'tutor'), 'teachers');
		$teacherList->display(); ?>
	</form>
</div>