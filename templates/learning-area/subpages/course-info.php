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
use Tutor\Components\Badge;
use Tutor\Components\Constants\Color;
use Tutor\Components\Constants\Variant;
use Tutor\Components\StarRating;
use Tutor\Components\SvgIcon;
use TUTOR\Icon;

// Globals inherited from learning-area/index.php template.
global $tutor_course_id,
$tutor_course,
$current_user_id;

$course_thumbnail       = get_tutor_course_thumbnail_src( 'full', $tutor_course_id );
$course_author          = get_user( $tutor_course->post_author );
$course_benefits        = tutor_course_benefits( $tutor_course_id );
$instructors            = tutor_utils()->get_instructors_by_course( $tutor_course_id );
$course_rating          = tutor_utils()->get_course_rating( $tutor_course_id );
$course_materials       = tutor_course_material_includes( $tutor_course_id );
$course_attachments     = tutor_utils()->get_attachments( $tutor_course_id );
$course_tags            = get_tutor_course_tags( $tutor_course_id );
$course_categories      = get_tutor_course_categories( $tutor_course_id );
$category_list          = ! empty( $course_categories ) && is_array( $course_categories )
						? implode( ', ', array_column( $course_categories, 'name' ) ) : '';
$course_requirements    = tutor_course_requirements( $tutor_course_id );
$course_target_audience = tutor_course_target_audience( $tutor_course_id );



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

// Helper function to add meta to the default meta array.
$add_meta = function ( string $icon, string $title, string $content ) use ( &$default_meta ) {
	if ( ! empty( $content ) ) {
		$default_meta[] = array(
			'icon'    => $icon,
			'title'   => $title,
			'content' => $content,
		);
	}
};

// Helper function to render list content.
$render_list = function ( $items ) {
	$list = implode(
		'',
		array_map(
			fn( $item ) => '<li>' . esc_html( $item ) . '</li>',
			$items
		)
	);
	return "<ul>{$list}</ul>";
};

// Course categories.
if ( ! empty( $category_list ) ) {
	$add_meta( Icon::CATEGORIES, __( 'Categories', 'tutor' ), $category_list );
}

if ( tutor_utils()->get_option( 'enable_course_total_enrolled' ) ) {

	$course_enrolled_count = sprintf(
		// translators: %s is the total enrolled users count.
		_n( '%s Student', '%s Students', tutor_utils()->count_enrolled_users_by_course(), 'tutor' ),
		tutor_utils()->count_enrolled_users_by_course()
	);

	$add_meta( Icon::PASSED, __( 'Total Enrolled', 'tutor' ), $course_enrolled_count );
}

if ( tutor_utils()->get_option( 'enable_course_level', true, true ) ) {
	$add_meta( Icon::LEVEL, __( 'Level', 'tutor' ), get_tutor_course_level( $tutor_course_id ) );
}

if ( tutor_utils()->get_option( 'enable_course_duration', true, true ) ) {
	$add_meta( Icon::TIME, __( 'Duration', 'tutor' ), get_tutor_course_duration_context( $tutor_course_id ) );
}

// Ratings.
$rating_content = StarRating::make()->count( $course_rating->rating_count )->rating( $course_rating->rating_avg )->show_average( true )->get();
$add_meta( Icon::RATINGS, __( 'Student Ratings', 'tutor' ), $rating_content );

// Resources.
$resource_content = sprintf(
	// translators: %s is the total attachments count.
	_n( '%s File', '%s Files', count( $course_attachments ), 'tutor' ),
	count( $course_attachments )
);
$add_meta( Icon::RESOURCES, __( 'Resources', 'tutor' ), $resource_content );

// Course Tags Contents.
ob_start();
if ( ! empty( $course_tags ) && is_array( $course_tags ) ) {
	?>
	<div class="tutor-flex tutor-items-start tutor-flex-wrap tutor-gap-3">
		<?php
		foreach ( $course_tags as $key => $tags ) {
			Badge::make()
			->label( $tags->name )
			->variant( Variant::SECONDARY )
			->render();
		}
		?>
	</div>
	<?php
}
$course_tag_contents = ob_get_clean();

if ( ! empty( $course_tag_contents ) ) {
	$add_meta( Icon::TAG, __( 'Tags', 'tutor' ), $course_tag_contents );
}

// Course materials.
if ( ! empty( $course_materials ) && is_array( $course_materials ) ) {
	$add_meta( Icon::MATERIAL, __( 'Materials', 'tutor' ), $render_list( $course_materials ) );
}

// Course Requirements.
if ( ! empty( $course_requirements ) && is_array( $course_requirements ) ) {
	$add_meta( Icon::REQUIREMENTS, __( 'Requirements', 'tutor' ), $render_list( $course_requirements ) );
}

// Course Target Audience.
if ( ! empty( $course_target_audience ) && is_array( $course_target_audience ) ) {
	$add_meta( Icon::AUDIENCE, __( 'Audience', 'tutor' ), $render_list( $course_target_audience ) );
}

$metadata = apply_filters( 'tutor_learning_area_course_info_metadata', $default_meta, $tutor_course_id );
?>
<div class="tutor-course-info tutor-pt-4" x-data="tutorCourseCompleteHandler">
	<?php do_action( 'tutor_learning_area_before_course_info', $tutor_course_id ); ?>

	<div class="tutor-course-thumb">
		<img src="<?php echo esc_url( $course_thumbnail ); ?>" alt="<?php echo esc_attr( $tutor_course->post_title ); ?>" />
	</div>

	<div class="tutor-course-intro">
		<div class="tutor-flex tutor-items-center tutor-justify-center tutor-gap-3 tutor-tiny tutor-text-secondary">
			<?php SvgIcon::make()->name( Icon::CALENDAR_CHECK )->color( Color::BRAND )->render(); ?>
			<?php
			echo esc_html(
				sprintf(
					// translators: %s is the course last modified date.
					__( '%s Last Updated', 'tutor' ),
					tutor_i18n_get_formated_date( $tutor_course->post_modified )
				)
			);
			?>
		</div>
		<h3 class="tutor-h3 tutor-sm-text-h5 tutor-mt-3"><?php echo esc_html( $tutor_course->post_title ); ?></h3>
		<div class="tutor-medium tutor-sm-text-small tutor-text-secondary tutor-mt-4 tutor-mb-6">
			<?php
			printf(
				wp_kses(
					// translators: %s is the linked course author name.
					__( 'by %s', 'tutor' ),
					array(
						'a' => array(
							'href'  => true,
							'class' => true,
						),
					)
				),
				sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( tutor_utils()->profile_url( $course_author, true ) ),
					esc_html( $course_author->display_name )
				)
			);
			?>
		</div>
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

	<div class="tutor-course-info-cards">
		<?php if ( ! empty( get_the_content() ) || ! empty( $course_benefits ) ) : ?>
			<?php if ( ! empty( get_the_content() ) ) : ?>
				<div class="tutor-card">
					<div class="tutor-medium tutor-font-medium">
						<?php esc_html_e( 'About this Course', 'tutor' ); ?>
					</div>
					<div class="tutor-p3">
						<?php the_content(); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $course_benefits ) && is_array( $course_benefits ) ) : ?>
				<div class="tutor-card">
					<div class="tutor-medium tutor-font-medium">
						<?php esc_html_e( "What you'll learn", 'tutor' ); ?>
					</div>
					<div class="tutor-course-info-list tutor-mt-6">
						<?php foreach ( $course_benefits as $benefit ) : ?>
							<div class="tutor-course-info-list-item">
								<?php SvgIcon::make()->name( Icon::CHECK_2 )->render(); ?>
								<div class="tutor-course-info-list-content">
									<?php echo esc_html( $benefit ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<div class="tutor-course-info-table tutor-table-wrapper tutor-table-bordered tutor-table-column-borders tutor-mt-4">
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
</div>
