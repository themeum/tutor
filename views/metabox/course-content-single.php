<?php 

    $counter = array(
        'lesson' => 0,
        'quiz' => 0,
        'assignment' => 0
    );

    foreach ($course_contents as $content){
        $attached_lesson_ids[] = $content->ID;

        if ($content->post_type === 'tutor_quiz'){
            $quiz = $content;
            $counter['quiz']++;
            tutor_load_template_from_custom_path(tutor()->path.'/views/fragments/quiz-list-single.php', array(
                'quiz_id' => $quiz->ID,
                'topic_id' => $topic->ID,
                'quiz_title' => __('Quiz', 'tutor').' '.$counter['quiz'].': '. $quiz->post_title,
            ), false);

        } elseif ($content->post_type === 'tutor_assignments'){
            $counter['assignment']++;
            ?>
            <div id="tutor-assignment-<?php echo $content->ID; ?>" class="course-content-item tutor-assignment tutor-assignment-<?php echo $content->ID; ?>">
                <div class="tutor-course-content-top">
                    <span class="color-text-hints tutor-icon-humnurger-filled tutor-font-size-24 tutor-pr-10"></span>
                    <a href="javascript:;" class="open-tutor-assignment-modal" data-assignment-id="<?php echo $content->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>">
                        <?php echo __('Assignment', 'tutor').' '.$counter['assignment'].': '. $content->post_title; ?>
                    </a>
                    <div class="tutor-course-content-top-right-action">
                        <a href="javascript:;" class="open-tutor-assignment-modal" data-assignment-id="<?php echo $content->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>">
                            <span class="color-text-hints tutor-icon-edit-filled tutor-font-size-24"></span>
                        </a>
                        <a href="javascript:;" class="tutor-delete-lesson-btn" data-lesson-id="<?php echo $content->ID; ?>">
                            <span class="color-text-hints tutor-icon-delete-stroke-filled tutor-font-size-24"></span>
                        </a>
                    </div>
                </div>
            </div>
            <?php
        } else if($content->post_type=='lesson') {
            $counter['lesson']++;
            ?>
            <div id="tutor-lesson-<?php echo $content->ID; ?>" class="course-content-item tutor-lesson tutor-lesson-<?php echo $content->ID; ?>">
                <div class="tutor-course-content-top">
                    <span class="color-text-hints tutor-icon-humnurger-filled tutor-font-size-24 tutor-pr-6"></span>
                    <a href="javascript:;" class="open-tutor-lesson-modal" data-lesson-id="<?php echo $content->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>">
                        <?php echo __('Lesson', 'tutor').' '.$counter['lesson'].': '.stripslashes($content->post_title); ?>
                    </a>
                    <div class="tutor-course-content-top-right-action">
                        <a href="javascript:;" class="open-tutor-lesson-modal" data-lesson-id="<?php echo $content->ID; ?>" data-topic-id="<?php echo $topic->ID; ?>">
                            <span class="color-text-hints tutor-icon-edit-filled tutor-font-size-24"></span>
                        </a>
                        <a href="javascript:;" class="tutor-delete-lesson-btn" data-lesson-id="<?php echo $content->ID; ?>">
                            <span class="color-text-hints tutor-icon-delete-stroke-filled tutor-font-size-24"></span>
                        </a>
                    </div>
                </div>
            </div>
            <?php
        } else {
            !isset($counter[$content->post_type]) ? $counter[$content->post_type]=0 : 0;
            $counter[$content->post_type]++;
            do_action( 'tutor/course/builder/content/'.$content->post_type, $content, $topic, $course_id, $counter[$content->post_type] );
        }
    }
?>