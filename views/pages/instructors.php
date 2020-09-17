<?php

$sub_page = tutor_utils()->avalue_dot('sub_page', $_GET);
if ( ! empty($sub_page)){
	$include_file = tutor()->path."views/pages/{$sub_page}.php";
	if (file_exists($include_file)){
		include $include_file;
		return;
	}
}

$instructorList = new \TUTOR\Instructors_List();
$instructorList->prepare_items();
?>


<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Instructors', 'tutor'); ?></h1>
    <a href="<?php echo add_query_arg(array('sub_page' => 'add_new_instructor')); ?>" class="page-title-action"><i class="tutor-icon-plus"></i>
		<?php _e('Add New Instructor', 'tutor');
		?></a>
    <hr class="wp-header-end">

    <form id="students-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$instructorList->search_box(__('Search', 'tutor'), 'instructors');
		$instructorList->display(); ?>
    </form>
</div>