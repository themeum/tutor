<?php
/**
 * Multi instructors view
 *
 * @package Tutor\Views
 * @subpackage Tutor\MetaBox
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

?>
<div class="tutor-course-instructors-metabox-wrap">
	<?php
	$instructors = tutor_utils()->get_instructors_by_course();
	?>

	<div class="tutor-course-available-instructors">
		<?php
		global $post;

		$instructor_crown_src = tutor()->url . 'assets/images/crown.svg';
		if ( is_array( $instructors ) && count( $instructors ) ) {
			foreach ( $instructors as $instructor ) {
				$author_tag = '';
				if ( $post->post_author == $instructor->ID ) {
					$author_tag = '<img src="' . esc_url( $instructor_crown_src ) . '"><i class="instructor-name-tooltip" title="' . __( 'Author', 'tutor' ) . '">' . __( 'Author', 'tutor' ) . '</i>';
				}
				?>
				<div id="added-instructor-id-<?php echo esc_attr( $instructor->ID ); ?>" class="added-instructor-item added-instructor-item-<?php echo esc_attr( $instructor->ID ); ?>" data-instructor-id="<?php echo esc_attr( $instructor->ID ); ?>">
					<?php
						echo wp_kses(
							tutor_utils()->get_tutor_avatar( $instructor->ID, 'md' ),
							tutor_utils()->allowed_avatar_tags()
						);
					?>
					<span class="instructor-name tutor-ml-12"> <?php echo esc_attr( $instructor->display_name ) . ' ' . $author_tag; //phpcs:ignore ?> </span>
					<span class="instructor-control">
						<a href="javascript:;" class="tutor-instructor-delete-btn tutor-iconic-btn">
							<i class="tutor-icon-times" area-hidden="true"></i>
						</a>
					</span>
				</div>
				<?php
			}
		}
		?>
	</div>

	<div class="tutor-add-instructor-button-wrap">
		<button type="button" class="tutor-btn tutor-btn-outline-primary tutor-add-instructor-btn"> <i class="tutor-icon-add-group tutor-mr-8"></i> <?php esc_html_e( 'Add More Instructors', 'tutor' ); ?> </button>
	</div>
</div>


<div class="tutor-modal-wrap tutor-instructors-modal-wrap">
	<div class="tutor-modal-content">
		<div class="modal-header">
			<div class="modal-title">
				<h1><?php esc_html_e( 'Add instructors', 'tutor' ); ?></h1>
			</div>
			<div class="lesson-modal-close-wrap">
				<a href="javascript:;" class="modal-close-btn"><i class="tutor-icon-times"></i></a>
			</div>
		</div>
		<div class="modal-content-body">

			<div class="search-bar">
				<input type="text" class="tutor-modal-search-input" placeholder="<?php esc_html_e( 'Search instructors...', 'tutor' ); ?>">
			</div>
		</div>
		<div class="tutor-modal-container"></div>
		<div class="modal-footer has-padding">
			<button type="button" class="tutor-btn add_instructor_to_course_btn"><?php esc_html_e( 'Add Instructors', 'tutor' ); ?></button>
		</div>
	</div>
</div>
