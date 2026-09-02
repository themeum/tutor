<?php
/**
 * Dashboard Section Manager
 *
 * Controller and renderer for instructor dashboard and analytics sections,
 * consuming InstructorMetricsAdapter to generate normalized data or HTML partials.
 *
 * @package Tutor
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 4.0.8
 */

namespace TUTOR;

defined( 'ABSPATH' ) || exit;

use TUTOR\InstructorMetricsAdapter;
use Tutor\Traits\JsonResponse;

/**
 * Class DashboardSectionManager
 *
 * @since 4.0.8
 */
class DashboardSectionManager {
	use JsonResponse;

	/**
	 * Constructor.
	 *
	 * @since 4.0.8
	 */
	public function __construct() {
		add_action( 'wp_ajax_tutor_get_dashboard_section', array( $this, 'ajax_get_dashboard_section' ) );
	}

	/**
	 * Get all registered dashboard and analytics sections metadata.
	 *
	 * @since 4.0.8
	 *
	 * @return array
	 */
	public static function get_registered_sections(): array {
		$sections = array(
			'current_stats'                => array(
				'title'          => __( 'Current Stats', 'tutor' ),
				'date_dependent' => true,
			),
			'overview_chart'               => array(
				'title'          => __( 'Earnings Over Time', 'tutor' ),
				'date_dependent' => true,
			),
			'course_completion_and_leader' => array(
				'title'          => __( 'Course Completion Rate', 'tutor' ),
				'date_dependent' => false,
			),
			'top_performing_courses'       => array(
				'title'          => __( 'Top Performing Courses', 'tutor' ),
				'date_dependent' => true,
				'sort_dependent' => true,
			),
			'upcoming_tasks_and_activity'  => array(
				'title'          => __( 'Upcoming Tasks', 'tutor' ),
				'date_dependent' => false,
			),
			'recent_reviews'               => array(
				'title'          => __( 'Recent Student Reviews', 'tutor' ),
				'date_dependent' => true,
			),
		);

		return apply_filters( 'tutor_dashboard_sections', $sections );
	}

	/**
	 * Get raw normalized domain data for a section via the Adapter.
	 *
	 * @since 4.0.8
	 *
	 * @param string $section_id Section identifier.
	 * @param array  $params     Context parameters (user_id, start_date, end_date, type, limit, etc.).
	 *
	 * @return mixed
	 */
	public static function get_section_data( string $section_id, array $params = array() ) {
		$user_id    = $params['user_id'] ?? get_current_user_id();
		$start_date = $params['start_date'] ?? '';
		$end_date   = $params['end_date'] ?? '';
		$type       = $params['type'] ?? 'revenue';
		$limit      = $params['limit'] ?? 3;

		switch ( $section_id ) {
			case 'current_stats':
				return InstructorMetricsAdapter::get_stat_cards( $user_id, $start_date, $end_date );

			case 'overview_chart':
				return InstructorMetricsAdapter::get_overview_chart_data( $user_id, $start_date, $end_date );

			case 'course_completion_and_leader':
				return InstructorMetricsAdapter::get_course_completion_distribution( $user_id );

			case 'top_performing_courses':
				return InstructorMetricsAdapter::get_top_performing_courses( $user_id, $type, $start_date, $end_date );

			case 'upcoming_tasks_and_activity':
				return InstructorMetricsAdapter::get_upcoming_tasks( $user_id );

			case 'recent_reviews':
				return InstructorMetricsAdapter::get_recent_reviews( $user_id, $limit, $start_date, $end_date );

			default:
				return apply_filters( 'tutor_dashboard_section_data', null, $section_id, $params );
		}
	}

	/**
	 * Get rendered HTML partial for a given section.
	 *
	 * @since 4.0.8
	 *
	 * @param string $section_id Section identifier.
	 * @param array  $params     Context parameters.
	 *
	 * @return string
	 */
	public static function get_section_html( string $section_id, array $params = array() ): string {
		$result = self::render_section( $section_id, $params );
		return $result['html'] ?? '';
	}

	/**
	 * Render a dashboard section, returning HTML, chart_data, and status payload.
	 *
	 * @since 4.0.8
	 *
	 * @param string $section_id Section identifier.
	 * @param array  $params     Context parameters.
	 *
	 * @return array
	 */
	public static function render_section( string $section_id, array $params = array() ): array {
		$registered = self::get_registered_sections();
		if ( ! isset( $registered[ $section_id ] ) ) {
			return array(
				'html'     => '',
				'has_data' => false,
			);
		}

		$params['user_id'] = $params['user_id'] ?? get_current_user_id();

		// Custom filter hook for third-party or Pro sections
		$custom_render = apply_filters( 'tutor_dashboard_section_render', null, $section_id, $params );
		if ( ! is_null( $custom_render ) ) {
			if ( is_array( $custom_render ) ) {
				return $custom_render;
			}
			return array(
				'html'     => (string) $custom_render,
				'has_data' => ! empty( $custom_render ),
			);
		}

		// If registered section has a custom callback (e.g. legacy/Pro registrations)
		if ( isset( $registered[ $section_id ]['callback'] ) && is_callable( $registered[ $section_id ]['callback'] ) ) {
			return call_user_func( $registered[ $section_id ]['callback'], $params );
		}

		$data = self::get_section_data( $section_id, $params );

		switch ( $section_id ) {
			case 'current_stats':
				return self::render_current_stats( $data, $params );

			case 'overview_chart':
				return self::render_overview_chart( $data, $params );

			case 'course_completion_and_leader':
				return self::render_course_completion( $data, $params );

			case 'top_performing_courses':
				return self::render_top_performing_courses( $data, $params );

			case 'upcoming_tasks_and_activity':
				return self::render_upcoming_tasks( $data, $params );

			case 'recent_reviews':
				return self::render_recent_reviews( $data, $params );

			default:
				return array(
					'html'     => '',
					'has_data' => ! empty( $data ),
				);
		}
	}

	/**
	 * Handle AJAX request to lazyload a dashboard section.
	 *
	 * @since 4.0.8
	 *
	 * @return void
	 */
	public function ajax_get_dashboard_section() {
		tutor_utils()->check_nonce();

		if ( ! User::is_admin() && ! tutor_utils()->is_instructor() ) {
			$this->response_bad_request( tutor_utils()->error_message() );
		}

		$section_id = Input::post( 'section', '', Input::TYPE_STRING );
		if ( empty( $section_id ) ) {
			$this->response_bad_request( __( 'Section identifier is required.', 'tutor' ) );
		}

		$registered = self::get_registered_sections();
		if ( ! isset( $registered[ $section_id ] ) ) {
			$this->response_bad_request( __( 'Invalid dashboard section.', 'tutor' ) );
		}

		$user_id    = get_current_user_id();
		$start_date = Input::post( 'start_date', '', Input::TYPE_STRING );
		$end_date   = Input::post( 'end_date', '', Input::TYPE_STRING );
		$type       = Input::post( 'top_performing_course', Input::post( 'type', 'revenue', Input::TYPE_STRING ), Input::TYPE_STRING );
		$type       = in_array( $type, array( 'revenue', 'student' ), true ) ? $type : 'revenue';

		$start_date = $start_date ? tutor_get_formated_date( 'Y-m-d', $start_date ) : '';
		$end_date   = $end_date ? tutor_get_formated_date( 'Y-m-d', $end_date ) : '';

		$params = array(
			'user_id'               => $user_id,
			'start_date'            => $start_date,
			'end_date'              => $end_date,
			'top_performing_course' => $type,
			'type'                  => $type,
		);

		try {
			$result = self::render_section( $section_id, $params );
			$this->response_data( $result );
		} catch ( \Throwable $e ) {
			$this->json_response( __( 'An error occurred while loading this section.', 'tutor' ), null, 500 );
		}
	}

	/* 
	=========================================================================
	SECTION VIEW RENDERERS
	=========================================================================
	*/

	/**
	 * Render Current Stats View.
	 *
	 * @since 4.0.8
	 *
	 * @param array $data   Stats cards from adapter.
	 * @param array $params Context parameters.
	 *
	 * @return array
	 */
	protected static function render_current_stats( $data, array $params ): array {
		$start_date = $params['start_date'] ?? '';
		$end_date   = $params['end_date'] ?? '';
		$cards      = is_array( $data ) ? $data : array();

		ob_start();
		?>
		<div class="tutor-flex tutor-flex-wrap tutor-gap-5 tutor-z-positive">
			<?php foreach ( $cards as $card ) : ?>
				<div class="tutor-flex-1">
					<?php
					tutor_load_template(
						'dashboard.instructor.analytics.stat-card',
						array(
							'variation'     => $card['variation'] ?? 'enrolled',
							'card_title'    => $card['title'] ?? '',
							'icon'          => $card['icon'] ?? '',
							'icon_size'     => $card['icon_size'] ?? 20,
							'value'         => $card['value'] ?? '',
							'content'       => $card['content'] ?? '',
							'hover_content' => $card['hover_content'] ?? array(),
							'start_date'    => $start_date,
							'end_date'      => $end_date,
						)
					);
					?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		$html = ob_get_clean();

		return array(
			'html'     => $html,
			'has_data' => ! empty( $cards ),
		);
	}

	/**
	 * Render Overview Chart View.
	 *
	 * @since 4.0.8
	 *
	 * @param array $data   Chart data from adapter.
	 * @param array $params Context parameters.
	 *
	 * @return array
	 */
	protected static function render_overview_chart( $data, array $params ): array {
		if ( empty( $data ) ) {
			return array(
				'html'     => '',
				'has_data' => false,
			);
		}

		ob_start();
		tutor_load_template(
			'dashboard.instructor.home.overview-chart',
			array(
				'overview_chart_data' => $data,
			)
		);
		$html = ob_get_clean();

		return array(
			'html'       => $html,
			'chart_data' => $data,
			'has_data'   => true,
		);
	}

	/**
	 * Render Course Completion View.
	 *
	 * @since 4.0.8
	 *
	 * @param array $data   Distribution data from adapter.
	 * @param array $params Context parameters.
	 *
	 * @return array
	 */
	protected static function render_course_completion( $data, array $params ): array {
		if ( empty( $data ) ) {
			return array(
				'html'     => '',
				'has_data' => false,
			);
		}

		ob_start();
		?>
		<div class="tutor-flex tutor-gap-6">
			<?php
			tutor_load_template(
				'dashboard.instructor.home.course-completion-chart',
				array(
					'course_completion_data' => $data,
				)
			);
			?>
		</div>
		<?php
		$html = ob_get_clean();

		return array(
			'html'       => $html,
			'chart_data' => $data,
			'has_data'   => true,
		);
	}

	/**
	 * Render Top Performing Courses View.
	 *
	 * @since 4.0.8
	 *
	 * @param array $data   Top courses data from adapter.
	 * @param array $params Context parameters.
	 *
	 * @return array
	 */
	protected static function render_top_performing_courses( $data, array $params ): array {
		$type        = $params['top_performing_course'] ?? $params['type'] ?? 'revenue';
		$top_courses = is_array( $data ) ? $data : array();

		if ( empty( $top_courses ) ) {
			return array(
				'html'     => '',
				'has_data' => false,
			);
		}

		ob_start();
		?>
		<div class="tutor-dashboard-home-card">
			<div class="tutor-flex tutor-row tutor-justify-between tutor-items-center tutor-gap-9">
				<div class="tutor-small">
					<?php esc_html_e( 'Top Performing Courses', 'tutor' ); ?>
				</div>

				<?php
				$filter_data = array(
					'options'  => array(
						'revenue' => __( 'Revenue', 'tutor' ),
						'student' => __( 'Student', 'tutor' ),
					),
					'selected' => $type,
				);
				tutor_load_template(
					'dashboard.instructor.home.top-performing-course-filter',
					$filter_data
				);
				?>
			</div>

			<div class="tutor-dashboard-home-card-body tutor-gap-4">
				<?php foreach ( $top_courses as $item_key => $item ) : ?>
					<?php
					tutor_load_template(
						'dashboard.instructor.home.top-performing-course-item',
						array(
							'item_key' => $item_key,
							'item'     => $item,
						)
					);
					?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		$html = ob_get_clean();

		return array(
			'html'     => $html,
			'has_data' => true,
		);
	}

	/**
	 * Render Upcoming Tasks View.
	 *
	 * @since 4.0.8
	 *
	 * @param array $data   Tasks data from adapter.
	 * @param array $params Context parameters.
	 *
	 * @return array
	 */
	protected static function render_upcoming_tasks( $data, array $params ): array {
		$tasks = is_array( $data ) ? $data : array();
		if ( empty( $tasks ) ) {
			return array(
				'html'     => '',
				'has_data' => false,
			);
		}

		ob_start();
		?>
		<div class="tutor-flex tutor-gap-6">
			<div class="tutor-dashboard-home-card tutor-flex-1">
				<div class="tutor-small">
					<?php esc_html_e( 'Upcoming Tasks', 'tutor' ); ?>
				</div>

				<div class="tutor-dashboard-home-card-body tutor-gap-4">
					<?php foreach ( $tasks as $item ) : ?>
						<?php
						tutor_load_template(
							'dashboard.instructor.home.upcoming-task-item',
							array( 'item' => $item )
						);
						?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
		$html = ob_get_clean();

		return array(
			'html'     => $html,
			'has_data' => true,
		);
	}

	/**
	 * Render Recent Reviews View.
	 *
	 * @since 4.0.8
	 *
	 * @param array $data   Reviews data from adapter.
	 * @param array $params Context parameters.
	 *
	 * @return array
	 */
	protected static function render_recent_reviews( $data, array $params ): array {
		$reviews = is_array( $data ) ? $data : array();
		if ( empty( $reviews ) ) {
			return array(
				'html'     => '',
				'has_data' => false,
			);
		}

		ob_start();
		?>
		<div class="tutor-dashboard-home-card">
			<div class="tutor-small">
				<?php esc_html_e( 'Recent Student Reviews', 'tutor' ); ?>
			</div>

			<div class="tutor-dashboard-home-card-body tutor-gap-6">
				<?php foreach ( $reviews as $review ) : ?>
					<?php
					tutor_load_template(
						'dashboard.instructor.home.recent-student-review-item',
						array( 'review' => $review )
					);
					?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		$html = ob_get_clean();

		return array(
			'html'     => $html,
			'has_data' => true,
		);
	}
}
