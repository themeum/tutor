<?php
/**
 * CourseFilter Component Class.
 *
 * Responsible for rendering a course filtering dropdown
 * with a search box to filter the list.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 4.0.0
 */

namespace Tutor\Components;

use TUTOR\Icon;
use TUTOR\Input;
use Tutor\Models\CourseModel;
use TUTOR\User;

defined( 'ABSPATH' ) || exit;

/**
 * Class CourseFilter
 *
 * Example Usage:
 * ```
 * CourseFilter::make()
 *     ->render();
 * ```
 *
 * @since 4.0.0
 */
class CourseFilter extends BaseComponent {

	/**
	 * List of courses
	 *
	 * @var array|null
	 */
	protected $courses = null;

	/**
	 * Current selected course ID
	 *
	 * @var int
	 */
	protected $current_course_id;

	/**
	 * Set courses list
	 *
	 * @param array $courses list of courses.
	 *
	 * @return self
	 */
	public function courses( array $courses ): self {
		$this->courses = $courses;
		return $this;
	}

	/**
	 * Set current course ID
	 *
	 * @param int $course_id current course ID.
	 *
	 * @return self
	 */
	public function current( int $course_id ): self {
		$this->current_course_id = $course_id;
		return $this;
	}

	/**
	 * Count of items
	 *
	 * @var int
	 */
	protected $count;

	/**
	 * Set count
	 *
	 * @param int $count count.
	 *
	 * @return self
	 */
	public function count( int $count ): self {
		$this->count = $count;
		return $this;
	}

	/**
	 * Get component content
	 *
	 * @return string
	 */
	public function get(): string {
		$courses         = $this->courses;
		$current_id      = $this->current_course_id;
		$selected_course = __( 'All', 'tutor' );

		// Fetch courses if not provided.
		if ( null === $courses ) {
			if ( User::is_admin() ) {
				$courses = CourseModel::get_courses();
			} elseif ( User::is_only_instructor() ) {
				$courses = CourseModel::get_courses_by_instructor();
			} else {
				$enrolled_courses = CourseModel::get_enrolled_courses_by_user();
				if ( ! empty( $enrolled_courses ) ) {
					$courses = $enrolled_courses->posts;
				}
			}
		}

		// Get current course ID from input if not set.
		if ( null === $current_id ) {
			$current_id = Input::get( 'course_id', 0, Input::TYPE_INT );
		}

		// Find selected course title.
		if ( ! empty( $courses ) ) {
			foreach ( $courses as $course ) {
				$course_id    = is_object( $course ) ? $course->ID : $course['ID'];
				$course_title = is_object( $course ) ? $course->post_title : $course['post_title'];

				if ( (int) $course_id === (int) $current_id ) {
					$selected_course = $course_title;
					break;
				}
			}
		}

		ob_start();
		?>
		<div
			class="tutor-course-filter"
			x-data="tutorPopover({
				placement: 'bottom-start',
				offset: 4,
			})"
		>
			<button 
				type="button" 
				x-ref="trigger" 
				@click="toggle()" 
				class="tutor-btn tutor-btn-link tutor-btn-small tutor-font-regular tutor-gap-2 tutor-p-none tutor-min-h-0"
			>
				<span class="tutor-truncate" style="max-width: 150px;">
					<?php echo esc_html( $selected_course ); ?>
				</span>
				<?php if ( null !== $this->count ) : ?>
					<span class="tutor-font-medium">
						(<?php echo esc_html( $this->count ); ?>)
					</span>
				<?php endif; ?>
				<?php tutor_utils()->render_svg_icon( Icon::CHEVRON_DOWN_2, 20, 20, array( 'class' => 'tutor-icon-secondary' ) ); ?>
			</button>

			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover"
			>
				<div class="tutor-popover-menu" x-data="{ search: '' }">
					<div class="tutor-input-field tutor-px-5 tutor-pt-2 tutor-pb-4">
						<div class="tutor-input-wrapper">
							<div class="tutor-input-content tutor-input-content-left">
								<?php tutor_utils()->render_svg_icon( Icon::SEARCH_2, 16, 16, array( 'class' => 'tutor-icon-idle' ) ); ?>
							</div>
							<input 
								type="text" 
								class="tutor-input tutor-input-sm tutor-input-content-left" 
								placeholder="<?php esc_attr_e( 'Search Course', 'tutor' ); ?>"
								x-model="search"
								@click.stop
							>
						</div>
					</div>

					<div class="tutor-overflow-y-auto" style="max-height: 200px;">
						<a 
							href="<?php echo esc_url( remove_query_arg( 'course_id' ) ); ?>" 
							class="tutor-popover-menu-item <?php echo 0 === $current_id ? 'tutor-active' : ''; ?>"
							x-show="search === '' || '<?php echo esc_js( __( 'All Courses', 'tutor' ) ); ?>'.toLowerCase().includes(search.toLowerCase())"
						>
							<?php esc_html_e( 'All Courses', 'tutor' ); ?>
						</a>

						<?php if ( ! empty( $courses ) ) : ?>
							<?php foreach ( $courses as $course ) : ?>
								<?php
								$course_id    = is_object( $course ) ? $course->ID : $course['ID'];
								$course_title = is_object( $course ) ? $course->post_title : $course['post_title'];
								$is_active    = (int) $course_id === (int) $current_id;
								?>
								<a 
									href="<?php echo esc_url( add_query_arg( 'course_id', $course_id ) ); ?>" 
									class="tutor-popover-menu-item <?php echo $is_active ? 'tutor-active' : ''; ?>"
									x-show="search === '' || '<?php echo esc_js( $course_title ); ?>'.toLowerCase().includes(search.toLowerCase())"
								>
									<span class="tutor-truncate">
										<?php echo esc_html( $course_title ); ?>
									</span>
								</a>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
