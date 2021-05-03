<?php

$allowed_sub_pages = array( 'add_new_instructor' );
$sub_page = sanitize_text_field( tutor_utils()->array_get('sub_page', $_GET, '') );

if ( is_string( $sub_page ) && in_array($sub_page, $allowed_sub_pages)){
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
	<?php 
		if(get_option( 'users_can_register', false ) && current_user_can( 'manage_options' )) {
			?>
			<a href="<?php echo add_query_arg(array('sub_page' => 'add_new_instructor')); ?>" class="page-title-action">
				<i class="tutor-icon-plus"></i> <?php _e('Add New Instructor', 'tutor'); ?>
			</a>
			<?php
		}
	?>
    <hr class="wp-header-end">

    <form id="students-filter" method="get">
        <input type="hidden" name="page" value="<?php echo \TUTOR\Instructors_List::INSTRUCTOR_LIST_PAGE; ?>" />
		<?php
		$instructorList->search_box(__('Search', 'tutor'), 'instructors');
		$instructorList->display(); ?>
    </form>
</div>