
<?php
$course = tutor_utils()->get_course_by_quiz(get_the_ID());
?>


<!---->
<!--<div class="tutor-single-page-top-bar">-->
<!--    <div class="tutor-topbar-item tutor-hide-sidebar-bar">-->
<!--        <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar"><i class="tutor-icon-menu-2"></i> </a>-->
<!--    </div>-->
<!---->
<!--    -->
<!---->
<!--    <div class="tutor-topbar-item tutor-topbar-content-title-wrap">-->
<!--		--><?php
//		tutor_utils()->get_lesson_type_icon(get_the_ID(), true, true);
//		the_title(); ?>
<!--    </div>-->
<!---->
<!--    <div class="tutor-topbar-item tutor-topbar-back-to-curse-wrap">-->
<!--        <a href="--><?php //echo get_the_permalink($course->ID); ?><!--">-->
<!--            <i class="tutor-icon-next-2"></i> --><?php //echo sprintf(__('Go to %s Course Home %s', 'tutor'), '<strong>', '</strong>') ; ?>
<!--        </a>-->
<!--    </div>-->
<!--</div>-->

<div class="tutor-single-page-top-bar">
    <div class="tutor-topbar-item tutor-hide-sidebar-bar">
        <a href="javascript:;" class="tutor-lesson-sidebar-hide-bar"><i class="tutor-icon-menu-2"></i> </a>
        <a href="<?php echo get_the_permalink($course->ID); ?>">
            <i class="tutor-icon-next-2"></i> <?php echo sprintf(__('Go to %s Course Home %s', 'tutor'), '<strong>', '</strong>') ; ?>
        </a>
    </div>
    <div class="tutor-topbar-item tutor-topbar-content-title-wrap">
        <?php
        tutor_utils()->get_lesson_type_icon(get_the_ID(), true, true);
        the_title(); ?>
    </div>

    <div class="tutor-topbar-item tutor-topbar-mark-to-done" style="width: 150px;"></div>
</div>


<div class="tutor-quiz-single-wrap ">
    <input type="hidden" name="tutor_quiz_id" id="tutor_quiz_id" value="<?php the_ID(); ?>">

	<?php
	if ($course){
		tutor_single_quiz_top();
		tutor_single_quiz_body();
	}else{
		tutor_single_quiz_no_course_belongs();
	}
	?>
</div>