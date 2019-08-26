<?php
$enrolmentList = new \TUTOR\Enrolments_List();
$enrolmentList->prepare_items();
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('Enrolments', 'tutor'); ?></h1>
    <a href="?page=enrolments&sub_page=enroll_student" class="page-title-action"><?php _e('Enroll a student', 'tutor'); ?></a>
    <hr class="wp-header-end">

    <div class="tnotice tnotice--blue">
        <div class="tnotice__icon">&iexcl;</div>
        <div class="tnotice__content">
            <p class="tnotice__type"><?php _e('Info', 'tutor'); ?></p>
            <p class="tnotice__message"><?php _e('You can search enrolments with enrol id, enrolment name, enrolment email, course title'); ?>.</p>
        </div>
        <!--<div class="tnotice__close">
            &times;
        </div>-->
    </div>

    <form id="enrolments-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$enrolmentList->search_box(__('Search', 'tutor'), 'enrolments');
		$enrolmentList->display(); ?>
	</form>
</div>