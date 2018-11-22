<?php
global $post;
$currentPost = $post;

$course = dozent_utils()->get_course_by_quiz(get_the_ID());
$previous_attempts = dozent_utils()->quiz_attempts();
$attempted_count = is_array($previous_attempts) ? count($previous_attempts) : 0;

$attempts_allowed = dozent_utils()->get_quiz_option(get_the_ID(), 'attempts_allowed', 0);
$passing_grade = dozent_utils()->get_quiz_option(get_the_ID(), 'passing_grade', 0);

$attempt_remaining = $attempts_allowed - $attempted_count;

do_action('dozent_quiz/single/before/top');

?>

<div class="dozent-quiz-header">
    <span class="dozent-quiz-badge"><?php _e('Quiz', 'dozent'); ?></span>
    <h2><?php echo get_the_title(); ?></h2>
    <h5>
        <?php _e('Course', 'dozent'); ?> :
        <a href="<?php echo get_the_permalink($course->ID); ?>"><?php echo get_the_title($course->ID); ?></a>
    </h5>
    <ul class="dozent-quiz-meta">

        <?php
            $total_questions = dozent_utils()->total_questions_for_student_by_quiz(get_the_ID());

            if($total_questions){
                ?>
                <li>
                    <strong><?php _e('Questions', 'dozent'); ?> :</strong>
                    <?php echo $total_questions; ?>
                </li>
                <?php
            }

            $time_limit = dozent_utils()->get_quiz_option(get_the_ID(), 'time_limit.time_value');
            if ($time_limit){
                $time_type = dozent_utils()->get_quiz_option(get_the_ID(), 'time_limit.time_type');
                ?>
                    <li>
                        <strong><?php _e('Time', 'dozent'); ?> :</strong>
                        <?php echo $time_limit.' '.$time_type; ?>
                    </li>
                <?php
            }

            if($attempts_allowed){
                ?>
                    <li>
                        <strong><?php _e('Attempts Allowed', 'dozent'); ?> :</strong>
                        <?php echo $attempts_allowed; ?>
                    </li>
                <?php
            }

            if($attempted_count){
                ?>
                    <li>
                        <strong><?php _e('Attemped', 'dozent'); ?> :</strong>
                        <?php echo $attempted_count; ?>
                    </li>
                <?php
            }

            if($attempt_remaining){
                ?>
                    <li>
                        <strong><?php _e('Attempts Remaining', 'dozent'); ?> :</strong>
                        <?php echo $attempt_remaining; ?>
                    </li>
                <?php
            }

            if($passing_grade){
                ?>
                    <li>
                        <strong><?php _e('Passing Grade', 'dozent'); ?> :</strong>
                        <?php echo $passing_grade . '%'; ?>

                    </li>
                <?php
            }
        ?>
    </ul>
</div>

<?php do_action('dozent_quiz/single/after/top'); ?>
