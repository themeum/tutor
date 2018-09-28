

<div class="lms-progress-bar">
	<div class="lms-progress-filled" style="width: 40%"></div>
	<span class="lms-progress-percent">50%</span>
</div>


<?php

$completed_count = lms_utils()->get_completed_lesson_count_by_course();

die(var_dump($completed_count));

?>