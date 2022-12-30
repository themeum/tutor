<?php
/**
 * Quiz context
 *
 * @package Tutor\Views
 * @subpackage Tutor\Quiz
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$contexts = array(
	'attempt-table'           => array(
		'columns'  => array(
			'checkbox'         => '<div class="tutor-d-flex"><input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input" /></div>',
			'date'             => __( 'Date', 'tutor' ),
			'quiz_info'        => __( 'Quiz Info', 'tutor' ),
			'course'           => __( 'Course', 'tutor' ),
			'question'         => __( 'Question', 'tutor' ),
			'total_marks'      => __( 'Total Marks', 'tutor' ),
			'correct_answer'   => __( 'Correct Answer', 'tutor' ),
			'incorrect_answer' => __( 'Incorrect Answer', 'tutor' ),
			'earned_marks'     => __( 'Earned Marks', 'tutor' ),
			'result'           => __( 'Result', 'tutor' ),
			'details'          => __( 'Details', 'tutor' ),
		),
		'contexts' => array(
			'frontend-dashboard-my-attempts'       => array(
				'quiz_info',
				'question',
				'total_marks',
				'correct_answer',
				'incorrect_answer',
				'earned_marks',
				'result',
				'details',
			),
			'frontend-dashboard-students-attempts' => 'frontend-dashboard-my-attempts',
			'course-single-previous-attempts'      => array(
				'date',
				'question',
				'total_marks',
				'correct_answer',
				'incorrect_answer',
				'earned_marks',
				'result',
				'details',
			),
			'backend-dashboard-students-attempts'  => array(
				'checkbox',
				'quiz_info',
				'course',
				'question',
				'total_marks',
				'correct_answer',
				'incorrect_answer',
				'earned_marks',
				'result',
				'details',
			),
		),
	),
	'attempt-details-summary' => array(
		'columns'  => array(
			'user'             => __( 'Attempt By', 'tutor' ),
			'date'             => __( 'Date', 'tutor' ),
			'qeustion_count'   => __( 'Question', 'tutor' ),
			'quiz_time'        => __( 'Quiz Time', 'tutor' ),
			'attempt_time'     => __( 'Attempt Time', 'tutor' ),
			'total_marks'      => __( 'Total Marks', 'tutor' ),
			'pass_marks'       => __( 'Pass Marks', 'tutor' ),
			'correct_answer'   => __( 'Correct Answer', 'tutor' ),
			'incorrect_answer' => __( 'Incorrect Answer', 'tutor' ),
			'earned_marks'     => __( 'Earned Marks', 'tutor' ),
			'result'           => __( 'Result', 'tutor' ),
		),
		'contexts' => array(
			'frontend-dashboard-my-attempts'       => array(
				'date',
				'qeustion_count',
				'total_marks',
				'pass_marks',
				'correct_answer',
				'incorrect_answer',
				'earned_marks',
				'result',
			),
			'frontend-dashboard-students-attempts' => 'frontend-dashboard-my-attempts',
			'course-single-previous-attempts'      => 'frontend-dashboard-my-attempts',
			'backend-dashboard-students-attempts'  => true,
		),
	),
	'attempt-details-answers' => array(
		'columns'  => array(
			'no'             => __( 'No', 'tutor' ),
			'type'           => __( 'Type', 'tutor' ),
			'questions'      => __( 'Questions', 'tutor' ),
			'given_answer'   => __( 'Given Answer', 'tutor' ),
			'correct_answer' => __( 'Correct Answer', 'tutor' ),
			'result'         => __( 'Result', 'tutor' ),
			'manual_review'  => __( 'Review', 'tutor' ),
		),
		'contexts' => array(
			'frontend-dashboard-my-attempts'       => array(
				'no',
				'type',
				'questions',
				'given_answer',
				'correct_answer',
				'result',
			),
			'frontend-dashboard-students-attempts' => array(
				'no',
				'type',
				'questions',
				'given_answer',
				'correct_answer',
				'result',
				'manual_review',
			),
			'backend-dashboard-students-attempts'  => 'frontend-dashboard-students-attempts',
			'course-single-previous-attempts'      => 'frontend-dashboard-my-attempts',
		),
	),
);

return tutor_utils()->get_table_columns_from_context( $page_key, $context, $contexts, 'tutor/quiz/attempts/table/column' );
