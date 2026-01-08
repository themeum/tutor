<?php
/**
 * PreviewTrigger Component Class.
 *
 * Responsible for rendering a preview trigger with a popover card.
 *
 * @package Tutor\Components
 * @author Themeum
 * @link https://themeum.com
 * @since 1.0.0
 */

namespace Tutor\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Class PreviewTrigger
 *
 * Example Usage:
 * ```
 * PreviewTrigger::make()
 *     ->id( 123 )
 *     ->render();
 * ```
 *
 * @since 1.0.0
 */
class PreviewTrigger extends BaseComponent {

	/**
	 * Content ID (Course or Lesson)
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Set content ID
	 *
	 * @param int $id Content ID.
	 *
	 * @return self
	 */
	public function id( int $id ): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * Get component content
	 *
	 * @return string
	 */
	public function get(): string {
		$id = $this->id;

		if ( ! $id ) {
			return '';
		}

		$post = get_post( $id );
		if ( ! $post ) {
			return '';
		}

		$post_type = $post->post_type;
		$title     = get_the_title( $id );
		$thumbnail = get_tutor_course_thumbnail_src( 'medium', $id );

		// Fallback for thumbnail if empty.
		if ( empty( $thumbnail ) ) {
			$thumbnail = tutor()->url . 'assets/images/placeholder.svg';
		}

		$instructor_name = '';
		$instructor_url  = '';

		// Determine instructor based on post type logic matching dashboard-notes.
		if ( tutor()->course_post_type === $post_type ) {
			$author_id       = $post->post_author;
			$instructor_name = tutor_utils()->display_name( $author_id );
			$instructor_url  = tutor_utils()->profile_url( $author_id, true );
		} elseif ( tutor()->lesson_post_type === $post_type ) {
			$course_id = tutor_utils()->get_course_id_by_lesson( $id );
			if ( $course_id ) {
				$course_post     = get_post( $course_id );
				$author_id       = $course_post->post_author;
				$instructor_name = tutor_utils()->display_name( $author_id );
				$instructor_url  = tutor_utils()->profile_url( $author_id, true );
			}
		}

		$preview_data = array(
			'title'          => $title,
			'url'            => get_permalink( $id ),
			'thumbnail'      => $thumbnail,
			'instructor'     => $instructor_name,
			'instructor_url' => $instructor_url,
		);

		ob_start();
		?>
		<div 
			x-data="tutorPreviewTrigger({ data: <?php echo esc_attr( wp_json_encode( $preview_data ) ); ?>, offset: 0 })"
			x-ref="trigger"
			class="tutor-preview-trigger"
		>
			<span class="tutor-preview-trigger-text"><?php echo esc_html( $title ); ?></span>
			<div 
				x-ref="content"
				x-show="open"
				x-cloak
				@click.outside="handleClickOutside()"
				class="tutor-popover tutor-preview-card"
			></div>
		</div>
		<?php
		return ob_get_clean();
	}
}
