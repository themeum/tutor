<?php
/**
 * Tutor learning area course info.
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.0
 */

defined( 'ABSPATH' ) || exit;

use Tutor\Components\Avatar;
use Tutor\Components\ConfirmationModal;
use Tutor\Components\StarRating;
use TUTOR\Course;
use TUTOR\Icon;
use Tutor\Components\SvgIcon;
use Tutor\Models\CourseModel;

// Globals inherited from learning-area/index.php template.
global $tutor_course_id,
$tutor_course,
$current_user_id;

$is_course_completed = tutor_utils()->is_completed_course( $tutor_course_id, $current_user_id );
$course_thumbnail    = get_tutor_course_thumbnail_src( 'full', $tutor_course_id );
$course_author       = get_user( $tutor_course->post_author );
$course_benefits     = tutor_course_benefits( $tutor_course_id );
$instructors         = tutor_utils()->get_instructors_by_course( $tutor_course_id );
$course_rating       = tutor_utils()->get_course_rating( $tutor_course_id );
$course_materials    = tutor_course_material_includes( $tutor_course_id );
$course_attachments  = tutor_utils()->get_attachments( $tutor_course_id );
$can_complete_course = CourseModel::can_complete_course( $tutor_course_id, $current_user_id ) && ! $is_course_completed;
$course_progress     = tutor_utils()->get_course_completed_percent( $tutor_course_id, $current_user_id );

$course_complete_modal_id = 'tutor-course-complete-modal';

ob_start();
foreach ( $instructors as $key => $instructor ) {
	?>
	<div class="tutor-flex tutor-items-center tutor-sm-items-start tutor-gap-5 tutor-sm-gap-4 tutor-mb-6">
		<?php Avatar::make()->user( $instructor->ID )->size( 'md' )->render(); ?>

		<div class="tutor-flex tutor-flex-column tutor-gap-2 tutor-sm-gap-1">
			<div class="tutor-medium tutor-font-medium tutor-sm-text-small">
				<?php echo esc_html( $instructor->display_name ); ?>
			</div>
			<?php if ( ! empty( $instructor->tutor_profile_job_title ) ) : ?>
			<div class="tutor-tiny tutor-text-secondary">
				<?php echo esc_html( $instructor->tutor_profile_job_title ); ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

$instructors_content = ob_get_clean();

$default_meta = array(
	array(
		'icon'    => Icon::INSTRUCTOR,
		'title'   => __( 'Instructors', 'tutor' ),
		'content' => $instructors_content,
	),
);

if ( tutor_utils()->get_option( 'enable_course_total_enrolled' ) ) {
	$default_meta[] = array(
		'icon'    => Icon::PASSED,
		'title'   => __( 'Total Enrolled', 'tutor' ),
		'content' => sprintf(
			// translators: %s is the total enrolled users count.
			_n( '%s Student', '%s Students', tutor_utils()->count_enrolled_users_by_course(), 'tutor' ),
			tutor_utils()->count_enrolled_users_by_course()
		),
	);
}

if ( tutor_utils()->get_option( 'enable_course_level', true, true ) ) {
	$default_meta[] = array(
		'icon'    => Icon::LEVEL,
		'title'   => __( 'Level', 'tutor' ),
		'content' => get_tutor_course_level( $tutor_course_id ),
	);
}

if ( tutor_utils()->get_option( 'enable_course_duration', true, true ) ) {
	$default_meta[] = array(
		'icon'    => Icon::TIME,
		'title'   => __( 'Duration', 'tutor' ),
		'content' => get_tutor_course_duration_context( $tutor_course_id ),
	);
}

$default_meta[] = array(
	'icon'    => Icon::RATINGS,
	'title'   => __( 'Student Ratings', 'tutor' ),
	'content' => StarRating::make()->count( $course_rating->rating_count )->rating( $course_rating->rating_avg )->show_average( true )->get(),
);

$default_meta[] = array(
	'icon'    => Icon::RESOURCES,
	'title'   => __( 'Resources', 'tutor' ),
	'content' => sprintf(
		// translators: %s is the total attachments count.
		_n( '%s File', '%s Files', count( $course_attachments ), 'tutor' ),
		count( $course_attachments )
	),
);

ob_start();
?>
<ul>
	<?php foreach ( $course_materials as $material ) : ?>
		<li><?php echo esc_html( $material ); ?></li>
	<?php endforeach; ?>
</ul>
<?php
$course_materials_content = ob_get_clean();
$default_meta[]           = array(
	'icon'    => Icon::MATERIAL,
	'title'   => __( 'Materials', 'tutor' ),
	'content' => $course_materials_content,
);

$metadata = apply_filters( 'tutor_learning_area_course_info_metadata', $default_meta, $tutor_course_id );
?>
<div class="tutor-course-info tutor-pt-7 tutor-pb-8" x-data="tutorCourseCompleteHandler">
	<?php do_action( 'tutor_learning_area_before_course_info', $tutor_course_id ); ?>

	<div class="tutor-course-thumb">
		<img src="<?php echo esc_url( $course_thumbnail ); ?>" alt="<?php echo esc_attr( $tutor_course->post_title ); ?>" />
	</div>

	<div class="tutor-course-intro">
		<div class="tutor-flex tutor-items-center tutor-justify-center tutor-gap-3 tutor-tiny tutor-text-secondary">
			<?php SvgIcon::make()->name( Icon::RELOAD_2 )->render(); ?>
			<?php echo esc_html( $tutor_course->post_modified ); ?> Last Updated
		</div>
		<h3 class="tutor-h3 tutor-sm-text-h5 tutor-mt-3"><?php echo esc_html( $tutor_course->post_title ); ?></h3>
		<div class="tutor-medium tutor-sm-text-small tutor-text-secondary tutor-mt-4 tutor-mb-6">
		<?php
		echo esc_html(
			sprintf(
				// translators: %s is the course author name.
				__( 'by %s', 'tutor' ),
				$course_author->display_name
			)
		);
		?>
		</div>
		<?php
		if ( $can_complete_course ) {
			Course::render_course_complete_btn( $course_complete_modal_id, $tutor_course_id, $course_progress );
		}
		?>
	</div>

	<!-- TODO: sticky behaviour -->
	<!-- <div class="tutor-course-sticky-card tutor-mt-9">
		<div class="tutor-course-thumb">
			<img src="<?php echo esc_url( $course_thumbnail ); ?>" alt="course thumb" />
		</div>
		<div class="tutor-flex tutor-flex-column tutor-gap-2">
			<h5 class="tutor-h5 tutor-sm-text-medium"><?php echo esc_html( $tutor_course->post_title ); ?></h5>
			<div class="tutor-medium tutor-sm-text-small tutor-text-secondary">
			<?php
			echo esc_html(
				sprintf(
				// translators: %s is the course author name.
					__( 'by %s', 'tutor' ),
					$course_author->display_name
				)
			);
			?>
		</div>
		</div>
	</div> -->

	<div class="tutor-course-description">
		<div x-data="{ expanded: false }" class="tutor-course-description-item">
			<div role="button" @click="expanded = !expanded" class="tutor-course-description-header">
				<div class="tutor-course-description-header-title">
					<?php esc_html_e( 'About this Course', 'tutor' ); ?>
				</div>
				<div class="tutor-course-description-header-icon" :class="{ 'is-expanded': expanded }">
					<?php SvgIcon::make()->name( Icon::CHEVRON_DOWN_2 )->size( 24 )->render(); ?>
				</div>
			</div>
			<div x-show="expanded" x-collapse x-cloak class="tutor-course-description-body">
				<?php echo wp_kses_post( $tutor_course->post_content ); ?>
			</div>
		</div>
		<div x-data="{ expanded: false }" class="tutor-course-description-item">
			<div role="button" @click="expanded = !expanded" class="tutor-course-description-header">
				<div class="tutor-course-description-header-title">
					<?php esc_html_e( "What you'll learn", 'tutor' ); ?>
				</div>
				<div class="tutor-course-description-header-icon" :class="{ 'is-expanded': expanded }">
					<?php SvgIcon::make()->name( Icon::CHEVRON_DOWN_2 )->size( 24 )->render(); ?>
				</div>
			</div>
			<div x-show="expanded" x-collapse x-cloak class="tutor-course-description-body">
				<div class="tutor-course-description-list">
					<?php foreach ( $course_benefits as $benefit ) : ?>
						<div class="tutor-course-description-list-item">
							<?php SvgIcon::make()->name( Icon::CHECK_2 )->render(); ?>
							<div class="tutor-course-description-list-content">
								<?php echo esc_html( $benefit ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="tutor-course-info-table tutor-table-wrapper tutor-table-bordered tutor-table-column-borders tutor-mt-6">
		<table class="tutor-table tutor-surface-l1">
			<?php foreach ( $metadata as $meta ) : ?>
				<tr>
					<td>
						<div class="tutor-flex tutor-items-center tutor-gap-4 tutor-sm-gap-3">
							<?php SvgIcon::make()->name( $meta['icon'] )->size( 20 )->render(); ?>
							<?php echo esc_html( $meta['title'] ); ?>
						</div>
					</td>

					<td>
					<?php
					add_filter( 'wp_kses_allowed_html', 'TUTOR\Input::allow_svg' );
					echo wp_kses_post( $meta['content'] );
					remove_filter( 'wp_kses_allowed_html', 'TUTOR\Input::allow_svg' );
					?>
					</td>
				</tr>
			<?php endforeach ?>
		</table>
	</div>

	<?php
	if ( $can_complete_course ) {
		$progress = $course_progress['completed_percent'] ?? 0;
		if ( $progress < 100 ) {
			ConfirmationModal::make()
			->id( $course_complete_modal_id )
			->title( __( 'Finish Course Early?', 'tutor' ) )
			->message( Course::get_complete_modal_content( $course_progress ), wp_kses_allowed_html( 'post' ) )
			->cancel_text( __( 'Go Back to Course', 'tutor' ) )
			->confirm_text( __( 'Complete Anyway', 'tutor' ) )
			->icon( Icon::WARNING_COLORIZED )
			->confirm_handler( "handleCourseComplete($tutor_course_id)" )
			->mutation_state( 'courseCompleteMutation' )
			->render();
		}
	}
	?>
</div>

