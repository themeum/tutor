<?php
/**
 * Question Answer Page
 *
 * @package Tutor\Templates
 * @subpackage Dashboard
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @version 1.6.4
 */

use TUTOR\Input;
use TUTOR\Instructor;

if ( isset( $_GET['question_id'] ) ) {
	tutor_load_template_from_custom_path(
		tutor()->path . '/views/qna/qna-single.php',
		array(
			'question_id' => Input::get( 'question_id' ),
			'context'     => 'frontend-dashboard-qna-single',
		)
	);
	return;
}

if ( in_array( Input::get( 'view_as' ), array( 'student', 'instructor' ) ) ) {
	update_user_meta( get_current_user_id(), 'tutor_qa_view_as', Input::get( 'view_as' ) );
}

$is_instructor     = tutor_utils()->is_instructor( null, true );
$view_option       = get_user_meta( get_current_user_id(), 'tutor_qa_view_as', true );
$view_as           = $is_instructor ? ( $view_option ? $view_option : 'instructor' ) : 'student';
$as_instructor_url = add_query_arg( array( 'view_as' => 'instructor' ), tutor()->current_url );
$as_student_url    = add_query_arg( array( 'view_as' => 'student' ), tutor()->current_url );
$qna_tabs          = \Tutor\Q_and_A::tabs_key_value( 'student' == $view_as ? get_current_user_id() : null );
$active_tab        = Input::get( 'tab', 'all' );
?>

<div class="tutor-frontend-dashboard-qna-header tutor-mb-32">
	<div class="tutor-row tutor-mb-24">
		<div class="tutor-col">
			<div class="tutor-fs-5 tutor-fw-medium tutor-color-black">
				<?php esc_html_e( 'Question & Answer', 'tutor' ); ?>
			</div>
		</div>
	
		<?php if ( $is_instructor ) : ?>
			<div class="tutor-col-auto">
				<label class="tutor-form-toggle tutor-dashboard-qna-vew-as tutor-d-flex tutor-justify-end current-view-<?php echo 'instructor' == $view_as ? 'instructor' : 'student'; ?>">
					<input type="checkbox" class="tutor-form-toggle-input" <?php echo 'instructor' == $view_as ? 'checked="checked"' : ''; ?> data-as_instructor_url="<?php echo esc_url( $as_instructor_url ); ?>" data-as_student_url="<?php echo esc_url( $as_student_url ); ?>" disabled="disabled" />
					<span class="tutor-form-toggle-label tutor-form-toggle-<?php echo 'student' == $view_as ? 'checked' : 'unchecked'; ?>"><?php esc_html_e( 'Student', 'tutor' ); ?></span>
					<span class="tutor-form-toggle-control"></span>
					<span class="tutor-form-toggle-label tutor-form-toggle-<?php echo 'instructor' == $view_as ? 'checked' : 'unchecked'; ?>"><?php esc_html_e( 'Instructor', 'tutor' ); ?></span>
				</label>
			</div>
		<?php endif; ?>
	</div>

		<div class="tutor-row">
			<div class="tutor-col-lg-5">
				<div class="tutor-qna-filter tutor-d-flex tutor-align-center">
					<span class="tutor-fs-7 tutor-color-secondary tutor-mr-20"><?php esc_html_e( 'Sort By', 'tutor' ); ?>:</span>
					<div class="tutor-flex-grow-1">
						<select class="tutor-form-select tutor-select-redirector">
							<?php
							foreach ( $qna_tabs as $tab ) {
								$markup = '<option value="' . $tab['url'] . '" ' . ( $active_tab == $tab['key'] ? 'selected="selected"' : '' ) . '>
                                        ' . $tab['title'] . '(' . $tab['value'] . ')' . '
                                    </option>';
								echo wp_kses(
									$markup,
									array(
										'option' => array(
											'value'    => true,
											'selected' => true,
										),
									)
								);
							}
							?>
						</select>
					</div>
				</div>
			</div>
		</div>
</div>

<?php
$per_page     = tutor_utils()->get_option( 'pagination_per_page', 10 );
$current_page = max( 1, tutor_utils()->avalue_dot( 'current_page', $_GET ) );
$offset       = ( $current_page - 1 ) * $per_page;

$q_status    = Input::get( 'tab' );
$asker_id    = 'instructor' == $view_as ? null : get_current_user_id();
$total_items = tutor_utils()->get_qa_questions( $offset, $per_page, '', null, null, $asker_id, $q_status, true );
$questions   = tutor_utils()->get_qa_questions( $offset, $per_page, '', null, null, $asker_id, $q_status );

tutor_load_template_from_custom_path(
	tutor()->path . '/views/qna/qna-table.php',
	array(
		'qna_list'       => $questions,
		'context'        => 'frontend-dashboard-qna-table-' . $view_as,
		'view_as'        => $view_as,
		'qna_pagination' => array(
			'base'        => '?current_page=%#%',
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'paged'       => $current_page,
		),
	)
);
?>
