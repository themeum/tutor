<?php
$instructorList = new \TUTOR\Instructors_List();
$instructorList->prepare_items();
?>


<div class="wrap">
	<h2><?php _e('Instructors', 'tutor'); ?></h2>

	<form id="students-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$instructorList->search_box(__('Search', 'tutor'), 'instructors');
		$instructorList->display(); ?>
	</form>
</div>