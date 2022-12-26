<?php
/**
 * Single attempt page
 *
 * @package Tutor\Views
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 2.0.0
 */

$passing_grade = tutor_utils()->get_quiz_option( get_the_ID(), 'passing_grade', 0 );
?>
<?php if ( ! empty( $back_url ) ) : ?>
	<div class="tutor-mb-24">
		<a class="tutor-btn tutor-btn-ghost" href="<?php echo esc_url( $back_url ); ?>">
			<span class="tutor-icon-previous tutor-mr-8" area-hidden="true"></span>
			<?php esc_html_e( 'Back', 'tutor' ); ?>
		</a>
	</div>
<?php endif; ?>

<div class="tutor-fs-7 tutor-color-secondary">
	<?php esc_html_e( 'Quiz', 'tutor' ); ?>
</div>

<div class="header-title tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mt-12 tutor-mb-32">
	<?php echo esc_html( $quiz_title ); ?>
</div>
<div class="tutor-d-flex tutor-justify-between tutor-py-20 tutor-my-20 tutor-border-top tutor-border-bottom">
	<div class="tutor-d-flex">
		<?php esc_html_e( 'Questions', 'tutor' ); ?>: 
		<span class="tutor-color-black">
			<?php echo esc_html( $question_count ); ?>
		</span>
	</div>
	<div class="tutor-d-flex-flex">
		<?php esc_html_e( 'Quiz Time', 'tutor' ); ?>: 
		<span class="tutor-color-black">
			<?php echo esc_html( $quiz_time ); ?>
		</span>
	</div>
	<div class="tutor-d-flex">
		<?php esc_html_e( 'Total Marks', 'tutor' ); ?>: 
		<span class="tutor-color-black">
			<?php echo esc_html( $earned_marks . '/' . $total_marks ); ?>
		</span>
	</div>
	<div class="tutor-d-flex">
		<?php esc_html_e( 'Passing Marks', 'tutor' ); ?>: 
		<span class="tutor-color-black">
			<?php
				$pass_marks = ( $total_marks * $passing_grade ) / 100;
				echo esc_html( number_format_i18n( $pass_marks, 2 ) );
			?>
		</span>
	</div>
</div>
